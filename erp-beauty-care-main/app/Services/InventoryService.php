<?php

namespace App\Services;

use App\Models\StockQuant;
use App\Models\StockMove;
use App\Models\Product;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderLine;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReceiptLine;
use App\Models\InternalTransfer;
use App\Models\ProductReturn;
use App\Models\ReturnLine;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Reserve stock for Sales Order (DL-02 / SR-04 / T-11 / T-12)
     */
    public static function reserveStock(string $variantSku, string $locationCode, int $qty, string $docId = ''): void
    {
        $quant = StockQuant::firstOrCreate(
            ['location_code' => $locationCode, 'variant_sku' => $variantSku],
            ['on_hand' => 0, 'reserved' => 0]
        );

        $available = $quant->on_hand - $quant->reserved;
        if ($qty > $available) {
            $msg = sprintf(
                'Insufficient stock available to reserve for %s at %s. Requested: %d, Available: %d (On Hand: %d, Reserved: %d)',
                $variantSku,
                $locationCode,
                $qty,
                $available,
                $quant->on_hand,
                $quant->reserved
            );
            AuditService::log('Reservation Blocked', 'StockQuant', $variantSku, $msg);
            throw new Exception($msg);
        }

        $quant->increment('reserved', $qty);
        AuditService::log('Reserve Stock', 'Sales Order', $docId, "Reserved {$qty} units of {$variantSku} at {$locationCode}");
    }

    /**
     * Internal Stock Transfer (DL-11 / SR-14 / T-27 / T-28)
     */
    public static function executeTransfer(InternalTransfer $transfer): void
    {
        if ($transfer->status === 'Done') {
            return; // Idempotent
        }

        $fromQuant = StockQuant::firstOrCreate(
            ['location_code' => $transfer->from_location, 'variant_sku' => $transfer->variant_sku],
            ['on_hand' => 0, 'reserved' => 0]
        );

        if ($transfer->qty > $fromQuant->on_hand) {
            $msg = sprintf(
                'Transfer rejected: Insufficient stock at source %s. Available to transfer: %d, Requested: %d',
                $transfer->from_location,
                $fromQuant->on_hand,
                $transfer->qty
            );
            AuditService::log('Transfer Blocked', 'Internal Transfer', $transfer->transfer_number, $msg);
            throw new Exception($msg);
        }

        DB::transaction(function () use ($transfer, $fromQuant) {
            $product = Product::findOrFail($transfer->variant_sku);
            $cost = (float) $product->cost;

            // 1. Decrement from source
            $fromQuant->decrement('on_hand', $transfer->qty);

            // 2. Increment destination
            $toQuant = StockQuant::firstOrCreate(
                ['location_code' => $transfer->to_location, 'variant_sku' => $transfer->variant_sku],
                ['on_hand' => 0, 'reserved' => 0]
            );
            $toQuant->increment('on_hand', $transfer->qty);

            // 3. Stock Move record
            StockMove::create([
                'move_number' => 'SM/INT/' . substr($transfer->transfer_number, -5),
                'reference_doc_type' => 'Internal Transfer',
                'reference_doc_id' => $transfer->transfer_number,
                'variant_sku' => $transfer->variant_sku,
                'from_location' => $transfer->from_location,
                'to_location' => $transfer->to_location,
                'qty' => $transfer->qty,
                'unit_cost' => $cost,
                'total_cost' => $cost * $transfer->qty,
                'status' => 'done',
                'is_quarantine' => false,
            ]);

            $transfer->update([
                'status' => 'Done',
                'transferred_at' => now(),
            ]);

            AuditService::log('Validate Transfer', 'Internal Transfer', $transfer->transfer_number, "Transferred {$transfer->qty} of {$transfer->variant_sku} from {$transfer->from_location} to {$transfer->to_location}");
        });
    }

    /**
     * Validate Delivery Order (DL-06, DL-08, F-09, T-13, T-14)
     */
    public static function validateDelivery(DeliveryOrder $do, array $deliveredQuantities = []): ?DeliveryOrder
    {
        // DL-08 / T-14 Idempotency Check
        if ($do->status === 'Done') {
            AuditService::log('Duplicate Validate Blocked', 'Delivery Order', $do->do_number, 'Delivery Order already validated');
            return null;
        }

        return DB::transaction(function () use ($do, $deliveredQuantities) {
            $totalCogs = 0;
            $hasBackorder = false;
            $backorderLines = [];

            foreach ($do->lines as $line) {
                $doneQty = isset($deliveredQuantities[$line->variant_sku])
                    ? (int) $deliveredQuantities[$line->variant_sku]
                    : $line->demand_qty;

                // Cannot deliver more than reserved
                if ($doneQty > $line->reserved_qty) {
                    throw new Exception("Delivered quantity ({$doneQty}) cannot exceed reserved quantity ({$line->reserved_qty}) for {$line->variant_sku}");
                }

                $backorderQty = $line->demand_qty - $doneQty;
                $line->update([
                    'done_qty' => $doneQty,
                    'backorder_qty' => $backorderQty,
                ]);

                $product = Product::findOrFail($line->variant_sku);
                $cost = (float) $product->cost;
                $lineCogs = $cost * $doneQty;
                $totalCogs += $lineCogs;

                // Update stock quants
                $quant = StockQuant::where('location_code', $do->source_location)
                    ->where('variant_sku', $line->variant_sku)
                    ->first();

                if ($quant) {
                    $quant->decrement('on_hand', $doneQty);
                    $quant->decrement('reserved', $doneQty);
                }

                // Create Stock Move
                StockMove::create([
                    'move_number' => 'SM/OUT/' . substr($do->do_number, -5) . '/' . $line->variant_sku,
                    'reference_doc_type' => 'Delivery Order',
                    'reference_doc_id' => $do->do_number,
                    'variant_sku' => $line->variant_sku,
                    'from_location' => $do->source_location,
                    'to_location' => null, // Customer
                    'qty' => $doneQty,
                    'unit_cost' => $cost,
                    'total_cost' => $lineCogs,
                    'status' => 'done',
                ]);

                // Update SO line delivered_qty
                if ($do->salesOrder) {
                    $soLine = $do->salesOrder->lines()->where('variant_sku', $line->variant_sku)->first();
                    if ($soLine) {
                        $soLine->increment('delivered_qty', $doneQty);
                    }
                }

                if ($backorderQty > 0) {
                    $hasBackorder = true;
                    $backorderLines[] = [
                        'variant_sku' => $line->variant_sku,
                        'demand_qty' => $backorderQty,
                        'reserved_qty' => 0,
                    ];
                }
            }

            // Journal Entry for COGS (F-15 / T-25)
            // Debit 5000 COGS / Credit 1200 Stock Valuation
            if ($totalCogs > 0) {
                $jeNum = 'JE/COGS/' . substr($do->do_number, -5);
                AccountingService::createJournalEntry(
                    $jeNum,
                    $do->do_number,
                    'Delivery Order',
                    $do->do_number,
                    [
                        [
                            'account_code' => '5000', // COGS
                            'debit' => $totalCogs,
                            'credit' => 0,
                            'description' => 'COGS for delivery ' . $do->do_number,
                        ],
                        [
                            'account_code' => '1200', // Stock Valuation
                            'debit' => 0,
                            'credit' => $totalCogs,
                            'description' => 'Stock decrease for ' . $do->do_number,
                        ],
                    ]
                );
            }

            $do->update([
                'status' => 'Done',
                'validated_at' => now(),
            ]);

            AuditService::log('Validate Delivery', 'Delivery Order', $do->do_number, "Delivery validated. COGS: Rp" . number_format($totalCogs, 0, ',', '.'));

            // Create Backorder if needed
            $backorderDo = null;
            if ($hasBackorder) {
                $boNumber = $do->do_number . '-BO';
                $backorderDo = DeliveryOrder::create([
                    'do_number' => $boNumber,
                    'sales_order_number' => $do->sales_order_number,
                    'customer_id' => $do->customer_id,
                    'source_location' => $do->source_location,
                    'status' => 'Confirmed',
                    'is_backorder' => true,
                    'origin_do_number' => $do->do_number,
                ]);

                foreach ($backorderLines as $bl) {
                    DeliveryOrderLine::create([
                        'do_number' => $boNumber,
                        'variant_sku' => $bl['variant_sku'],
                        'demand_qty' => $bl['demand_qty'],
                        'reserved_qty' => $bl['reserved_qty'],
                        'done_qty' => 0,
                        'backorder_qty' => 0,
                    ]);
                }

                AuditService::log('Create Backorder', 'Delivery Order', $boNumber, "Backorder created for {$do->do_number}");
            }

            return $backorderDo;
        });
    }

    /**
     * Validate Purchase Receipt with Accepted & Rejected Quantities (DL-03 / SR-06 / F-13 / T-21 / T-22)
     */
    public static function validateReceipt(PurchaseReceipt $receipt, array $inspections): void
    {
        if ($receipt->status === 'Done') {
            return; // Idempotent
        }

        DB::transaction(function () use ($receipt, $inspections) {
            $totalValuedCost = 0;

            foreach ($receipt->lines as $line) {
                $insp = $inspections[$line->variant_sku] ?? ['accepted_qty' => $line->ordered_qty, 'rejected_qty' => 0];
                $accepted = (int) $insp['accepted_qty'];
                $rejected = (int) $insp['rejected_qty'];

                if (($accepted + $rejected) !== $line->ordered_qty) {
                    throw new Exception("Total of accepted ({$accepted}) and rejected ({$rejected}) must equal ordered qty ({$line->ordered_qty})");
                }

                $line->update([
                    'accepted_qty' => $accepted,
                    'rejected_qty' => $rejected,
                ]);

                $product = Product::findOrFail($line->variant_sku);
                $cost = (float) $product->cost;

                // 1. Accepted qty increases WH-JKT on_hand
                if ($accepted > 0) {
                    $quant = StockQuant::firstOrCreate(
                        ['location_code' => $receipt->destination_location, 'variant_sku' => $line->variant_sku],
                        ['on_hand' => 0, 'reserved' => 0]
                    );
                    $quant->increment('on_hand', $accepted);

                    $valCost = $cost * $accepted;
                    $totalValuedCost += $valCost;

                    StockMove::create([
                        'move_number' => 'SM/IN/' . substr($receipt->receipt_number, -5) . '/' . $line->variant_sku,
                        'reference_doc_type' => 'Purchase Receipt',
                        'reference_doc_id' => $receipt->receipt_number,
                        'variant_sku' => $line->variant_sku,
                        'from_location' => null, // Vendor
                        'to_location' => $receipt->destination_location,
                        'qty' => $accepted,
                        'unit_cost' => $cost,
                        'total_cost' => $valCost,
                        'status' => 'done',
                        'is_quarantine' => false,
                    ]);
                }

                // 2. Rejected qty goes to Quarantine area (non-valued)
                if ($rejected > 0) {
                    StockMove::create([
                        'move_number' => 'SM/QUAR/' . substr($receipt->receipt_number, -5) . '/' . $line->variant_sku,
                        'reference_doc_type' => 'Purchase Receipt',
                        'reference_doc_id' => $receipt->receipt_number,
                        'variant_sku' => $line->variant_sku,
                        'from_location' => null,
                        'to_location' => 'QUARANTINE',
                        'qty' => $rejected,
                        'unit_cost' => 0,
                        'total_cost' => 0,
                        'status' => 'done',
                        'is_quarantine' => true,
                    ]);
                }

                // Update PO line qty_received
                if ($receipt->purchaseOrder) {
                    $poLine = $receipt->purchaseOrder->lines()->where('variant_sku', $line->variant_sku)->first();
                    if ($poLine) {
                        $poLine->increment('qty_received', $accepted);
                    }
                }
            }

            // Journal for Stock Received: Debit 1200 Stock Valuation / Credit 1250 Stock Interim Received
            if ($totalValuedCost > 0) {
                $jeNum = 'JE/REC/' . substr($receipt->receipt_number, -5);
                AccountingService::createJournalEntry(
                    $jeNum,
                    $receipt->receipt_number,
                    'Purchase Receipt',
                    $receipt->receipt_number,
                    [
                        [
                            'account_code' => '1200', // Stock Valuation
                            'debit' => $totalValuedCost,
                            'credit' => 0,
                            'description' => 'Inventory receipt from ' . $receipt->vendor_id,
                        ],
                        [
                            'account_code' => '1250', // Stock Interim Received
                            'debit' => 0,
                            'credit' => $totalValuedCost,
                            'description' => 'Stock Interim Received for ' . $receipt->receipt_number,
                        ],
                    ]
                );
            }

            $receipt->update([
                'status' => 'Done',
                'validated_at' => now(),
            ]);

            AuditService::log('Validate Receipt', 'Purchase Receipt', $receipt->receipt_number, "Receipt validated. Value added: Rp" . number_format($totalValuedCost, 0, ',', '.'));
        });
    }

    /**
     * Process Return and Issue Credit Note (DL-07 / SR-10 / F-18 / T-29 / T-30)
     */
    public static function processReturn(ProductReturn $return): void
    {
        if ($return->status === 'Done') {
            return; // Idempotent
        }

        DB::transaction(function () use ($return) {
            $totalCreditNoteUntaxed = 0;
            $totalCreditNoteTax = 0;
            $totalReversedCogs = 0;

            foreach ($return->lines as $line) {
                $maxReturnable = $line->delivered_qty - $line->already_returned_qty;
                if ($line->return_qty > $maxReturnable) {
                    $msg = sprintf(
                        'Return rejected: Return qty (%d) exceeds available delivered quantity (%d) for %s',
                        $line->return_qty,
                        $maxReturnable,
                        $line->variant_sku
                    );
                    AuditService::log('Return Blocked', 'Return', $return->return_number, $msg);
                    throw new Exception($msg);
                }

                $product = Product::findOrFail($line->variant_sku);
                $cost = (float) $product->cost;

                $untaxed = (float) ($line->unit_price * $line->return_qty);
                $tax = AccountingService::calculateLineTax((float) $line->unit_price, $line->return_qty);
                $subtotal = $untaxed + $tax;

                $line->update([
                    'unit_cost' => $cost,
                    'tax_amount' => $tax,
                    'subtotal' => $subtotal,
                ]);

                $totalCreditNoteUntaxed += $untaxed;
                $totalCreditNoteTax += $tax;
                $lineCogs = $cost * $line->return_qty;
                $totalReversedCogs += $lineCogs;

                // If inspection is Sealed / Accepted, return to WH-JKT stock!
                if ($return->inspection_status === 'Sealed / Accepted') {
                    $quant = StockQuant::firstOrCreate(
                        ['location_code' => 'WH-JKT', 'variant_sku' => $line->variant_sku],
                        ['on_hand' => 0, 'reserved' => 0]
                    );
                    $quant->increment('on_hand', $line->return_qty);

                    StockMove::create([
                        'move_number' => 'SM/RET/' . substr($return->return_number, -5) . '/' . $line->variant_sku,
                        'reference_doc_type' => 'Return',
                        'reference_doc_id' => $return->return_number,
                        'variant_sku' => $line->variant_sku,
                        'from_location' => null, // Customer return
                        'to_location' => 'WH-JKT',
                        'qty' => $line->return_qty,
                        'unit_cost' => $cost,
                        'total_cost' => $lineCogs,
                        'status' => 'done',
                    ]);
                }
            }

            $creditNoteTotal = $totalCreditNoteUntaxed + $totalCreditNoteTax;

            // 1. Reversal Journal for COGS: Debit 1200 Stock Valuation / Credit 5000 COGS
            if ($totalReversedCogs > 0 && $return->inspection_status === 'Sealed / Accepted') {
                $jeCogs = 'JE/RET-COGS/' . substr($return->return_number, -5);
                AccountingService::createJournalEntry(
                    $jeCogs,
                    $return->return_number,
                    'Return COGS Reversal',
                    $return->return_number,
                    [
                        [
                            'account_code' => '1200', // Stock Valuation
                            'debit' => $totalReversedCogs,
                            'credit' => 0,
                            'description' => 'Reverse stock valuation for return ' . $return->return_number,
                        ],
                        [
                            'account_code' => '5000', // COGS
                            'debit' => 0,
                            'credit' => $totalReversedCogs,
                            'description' => 'Reverse COGS for return ' . $return->return_number,
                        ],
                    ]
                );
            }

            // 2. Credit Note Journal: Debit 4000 Revenue, Debit 2150 Output VAT, Credit 1100 AR
            $jeCn = 'JE/CN/' . substr($return->return_number, -5);
            AccountingService::createJournalEntry(
                $jeCn,
                $return->origin_invoice_number,
                'Credit Note',
                $return->return_number,
                [
                    [
                        'account_code' => '4000', // Sales Revenue
                        'debit' => $totalCreditNoteUntaxed,
                        'credit' => 0,
                        'description' => 'Credit Note Sales Reversal',
                    ],
                    [
                        'account_code' => '2150', // Output VAT
                        'debit' => $totalCreditNoteTax,
                        'credit' => 0,
                        'description' => 'Credit Note Output VAT Reversal',
                    ],
                    [
                        'account_code' => '1100', // Accounts Receivable
                        'debit' => 0,
                        'credit' => $creditNoteTotal,
                        'description' => 'Credit Note AR Adjustment for ' . $return->return_number,
                    ],
                ]
            );

            // Update original invoice open amount
            if ($return->originInvoice) {
                $newOpen = max(0, (float) $return->originInvoice->open_amount - $creditNoteTotal);
                $return->originInvoice->update(['open_amount' => $newOpen]);
            }

            $return->update([
                'status' => 'Done',
                'credit_note_total' => $creditNoteTotal,
                'completed_at' => now(),
            ]);

            AuditService::log('Validate Return', 'Return', $return->return_number, "Return processed. Credit note issued: Rp" . number_format($creditNoteTotal, 0, ',', '.'));
        });
    }
}
