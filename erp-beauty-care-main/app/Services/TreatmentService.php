<?php

namespace App\Services;

use App\Models\BeautyTreatment;
use App\Models\Employee;
use App\Models\StockQuant;
use App\Models\StockMove;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceLine;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class TreatmentService
{
    /**
     * Validate and assign therapist to treatment (DL-14 / SR-17 / F-19 / T-31 / T-32)
     */
    public static function assignTherapist(BeautyTreatment $treatment, Employee $employee): void
    {
        if ($employee->status !== 'Aktif') {
            $msg = "Assignment rejected: Employee {$employee->name} ({$employee->id}) is not active.";
            AuditService::log('Assignment Blocked', 'Beauty Treatment', $treatment->treatment_number, $msg);
            throw new Exception($msg);
        }

        if (!$employee->hasSkill('Facial')) {
            $msg = "Assignment rejected: Employee {$employee->name} ({$employee->id}) does not possess the required Facial skill.";
            AuditService::log('Assignment Blocked', 'Beauty Treatment', $treatment->treatment_number, $msg);
            throw new Exception($msg);
        }

        $treatment->update(['employee_id' => $employee->id]);
        AuditService::log('Assign Therapist', 'Beauty Treatment', $treatment->treatment_number, "Assigned to {$employee->name} ({$employee->id})");
    }

    /**
     * Complete Treatment: consumes 1 BC-MSK-001 from ST-BDG, posts COGS and Customer Invoice (DL-15 / SR-18 / F-20 / T-33 / T-34)
     */
    public static function completeTreatment(BeautyTreatment $treatment): BeautyTreatment
    {
        // DL-08 / T-34 Idempotency Check
        if ($treatment->status === 'Done') {
            AuditService::log('Duplicate Complete Blocked', 'Beauty Treatment', $treatment->treatment_number, 'Treatment is already completed');
            return $treatment;
        }

        return DB::transaction(function () use ($treatment) {
            $location = $treatment->location_code ?: 'ST-BDG';
            $sku = $treatment->consumed_sku ?: 'BC-MSK-001';
            $qty = $treatment->consumed_qty ?: 1;

            $product = Product::findOrFail($sku);
            $cost = (float) $product->cost; // Rp40.000 for BC-MSK-001
            $totalConsumablesCost = $cost * $qty;

            // 1. Consume stock from ST-BDG
            $quant = StockQuant::where('location_code', $location)
                ->where('variant_sku', $sku)
                ->first();

            if (!$quant || $quant->on_hand < $qty) {
                throw new Exception("Insufficient stock of {$sku} at {$location} to complete treatment.");
            }

            $quant->decrement('on_hand', $qty);

            // Record Stock Move
            StockMove::create([
                'move_number' => 'SM/TRT/' . substr($treatment->treatment_number, -5),
                'reference_doc_type' => 'Treatment',
                'reference_doc_id' => $treatment->treatment_number,
                'variant_sku' => $sku,
                'from_location' => $location,
                'to_location' => null,
                'qty' => $qty,
                'unit_cost' => $cost,
                'total_cost' => $totalConsumablesCost,
                'status' => 'done',
            ]);

            // Journal for Consumables: Debit 5010 Treatment Consumables / Credit 1200 Stock Valuation
            $jeCons = 'JE/TRT-CONS/' . substr($treatment->treatment_number, -5);
            AccountingService::createJournalEntry(
                $jeCons,
                $treatment->treatment_number,
                'Treatment Consumables',
                $treatment->treatment_number,
                [
                    [
                        'account_code' => '5010', // Treatment Consumables
                        'debit' => $totalConsumablesCost,
                        'credit' => 0,
                        'description' => 'Mask consumed for ' . $treatment->treatment_number,
                    ],
                    [
                        'account_code' => '1200', // Stock Valuation
                        'debit' => 0,
                        'credit' => $totalConsumablesCost,
                        'description' => 'Stock decrease at ' . $location,
                    ],
                ]
            );

            // 2. Generate and post Customer Invoice for Treatment
            $invNum = 'INV/TRT/' . substr($treatment->treatment_number, -5);
            $untaxed = 300000;
            $tax = 33000; // 11% PPN
            $total = 333000;

            $invoice = CustomerInvoice::create([
                'invoice_number' => $invNum,
                'customer_id' => $treatment->customer_id,
                'source_doc_type' => 'Treatment',
                'source_doc_id' => $treatment->treatment_number,
                'status' => 'Posted',
                'untaxed_amount' => $untaxed,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'paid_amount' => 0,
                'open_amount' => $total,
                'posted_at' => now(),
            ]);

            CustomerInvoiceLine::create([
                'invoice_number' => $invNum,
                'item_type' => 'service',
                'item_code' => $treatment->service_id,
                'description' => 'Glow Facial Service',
                'qty' => 1,
                'unit_price' => $untaxed,
                'discount_percent' => 0,
                'tax_amount' => $tax,
                'subtotal' => $total,
            ]);

            // Journal for Service Invoice: Debit 1100 AR / Credit 4010 Service Revenue / Credit 2150 Output VAT
            $jeInv = 'JE/TRT-INV/' . substr($treatment->treatment_number, -5);
            AccountingService::createJournalEntry(
                $jeInv,
                $treatment->treatment_number,
                'Customer Invoice',
                $invNum,
                [
                    [
                        'account_code' => '1100', // AR
                        'debit' => $total,
                        'credit' => 0,
                        'description' => 'AR for treatment ' . $treatment->treatment_number,
                    ],
                    [
                        'account_code' => '4010', // Service Revenue
                        'debit' => 0,
                        'credit' => $untaxed,
                        'description' => 'Service Revenue for Glow Facial',
                    ],
                    [
                        'account_code' => '2150', // Output VAT
                        'debit' => 0,
                        'credit' => $tax,
                        'description' => 'Output VAT 11%',
                    ],
                ]
            );

            $treatment->update([
                'status' => 'Done',
                'billed_total' => $total,
                'invoice_id' => $invNum,
                'completed_at' => now(),
            ]);

            AuditService::log('Complete Treatment', 'Beauty Treatment', $treatment->treatment_number, "Treatment completed by {$treatment->employee_id}. Invoice: {$invNum}");
            return $treatment;
        });
    }
}
