<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceLine;
use App\Models\VendorBill;
use App\Models\VendorBillLine;
use App\Models\Payment;
use App\Models\Contact;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class AccountingService
{
    /**
     * Compute effective 11% PPN rounded to nearest Rp 1 per line
     * (DL-13 / SR-16)
     */
    public static function calculateLineTax(float $unitPrice, int $qty, float $discountPercent = 0): float
    {
        $base = ($unitPrice * $qty) * (1 - ($discountPercent / 100));
        return (float) round($base * 0.11);
    }

    /**
     * Post a balanced journal entry idempotently (DL-08, DL-13)
     */
    public static function createJournalEntry(
        string $entryNumber,
        string $reference,
        string $sourceDocType,
        string $sourceDocId,
        array $items,
        ?string $date = null,
        ?string $postedBy = null
    ): JournalEntry {
        // Check if already exists (Idempotency DL-08 / T-26)
        $existing = JournalEntry::find($entryNumber);
        if ($existing) {
            AuditService::log('Duplicate Post Blocked', 'Journal Entry', $entryNumber, 'Idempotency prevented duplicate posting');
            return $existing;
        }

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($items as $item) {
            $totalDebit += (float) ($item['debit'] ?? 0);
            $totalCredit += (float) ($item['credit'] ?? 0);
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new Exception("Journal entry must balance! Debit: {$totalDebit}, Credit: {$totalCredit}");
        }

        return DB::transaction(function () use ($entryNumber, $reference, $sourceDocType, $sourceDocId, $items, $date, $totalDebit, $totalCredit, $postedBy) {
            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'date' => $date ?? date('Y-m-d'),
                'reference' => $reference,
                'source_doc_type' => $sourceDocType,
                'source_doc_id' => $sourceDocId,
                'status' => 'Posted',
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'posted_by' => $postedBy ?? 'Finance User',
                'posted_at' => now(),
            ]);

            foreach ($items as $item) {
                JournalItem::create([
                    'entry_number' => $entry->entry_number,
                    'account_code' => $item['account_code'],
                    'debit' => $item['debit'] ?? 0,
                    'credit' => $item['credit'] ?? 0,
                    'description' => $item['description'] ?? $reference,
                ]);
            }

            AuditService::log('Post', 'Journal Entry', $entryNumber, "Journal posted for {$sourceDocType} {$sourceDocId}");
            return $entry;
        });
    }

    /**
     * Post Customer Invoice (DL-01, DL-13, F-10, T-15)
     */
    public static function postCustomerInvoice(CustomerInvoice $invoice): CustomerInvoice
    {
        if ($invoice->status === 'Posted') {
            return $invoice; // Idempotent
        }

        return DB::transaction(function () use ($invoice) {
            $entryNumber = 'JE/INV/' . substr($invoice->invoice_number, -5);
            $reference = $invoice->delivery_order_number ?: $invoice->invoice_number;

            $items = [
                [
                    'account_code' => '1100', // Accounts Receivable
                    'debit' => (float) $invoice->total_amount,
                    'credit' => 0,
                    'description' => 'Customer Invoice ' . $invoice->invoice_number,
                ],
                [
                    'account_code' => '4000', // Sales Revenue
                    'debit' => 0,
                    'credit' => (float) $invoice->untaxed_amount,
                    'description' => 'Sales Revenue',
                ],
                [
                    'account_code' => '2150', // Output VAT
                    'debit' => 0,
                    'credit' => (float) $invoice->tax_amount,
                    'description' => 'Output VAT 11%',
                ],
            ];

            self::createJournalEntry(
                $entryNumber,
                $reference,
                'Customer Invoice',
                $invoice->invoice_number,
                $items,
                date('Y-m-d'),
                'Finance User'
            );

            $invoice->update([
                'status' => 'Posted',
                'open_amount' => $invoice->total_amount,
                'posted_at' => now(),
            ]);

            AuditService::log('Post', 'Customer Invoice', $invoice->invoice_number, 'Invoice posted');
            return $invoice;
        });
    }

    /**
     * Post Vendor Bill (DL-03, F-14, T-23)
     */
    public static function postVendorBill(VendorBill $bill): VendorBill
    {
        if ($bill->status === 'Posted') {
            return $bill; // Idempotent
        }

        return DB::transaction(function () use ($bill) {
            $entryNumber = 'JE/BILL/' . substr($bill->bill_number, -5);
            $reference = $bill->po_number;

            $items = [
                [
                    'account_code' => '1250', // Stock Interim Received
                    'debit' => (float) $bill->untaxed_amount,
                    'credit' => 0,
                    'description' => 'Stock Interim Clearing',
                ],
                [
                    'account_code' => '1150', // Input VAT
                    'debit' => (float) $bill->tax_amount,
                    'credit' => 0,
                    'description' => 'Input VAT 11%',
                ],
                [
                    'account_code' => '2100', // Accounts Payable
                    'debit' => 0,
                    'credit' => (float) $bill->total_amount,
                    'description' => 'Accounts Payable',
                ],
            ];

            self::createJournalEntry(
                $entryNumber,
                $reference,
                'Vendor Bill',
                $bill->bill_number,
                $items,
                date('Y-m-d'),
                'Finance User'
            );

            $bill->update([
                'status' => 'Posted',
                'open_amount' => $bill->total_amount,
                'posted_at' => now(),
            ]);

            AuditService::log('Post', 'Vendor Bill', $bill->bill_number, 'Vendor Bill posted');
            return $bill;
        });
    }

    /**
     * Register Customer Payment (DL-12, SR-15, F-16, T-17, T-18)
     */
    public static function registerCustomerPayment(CustomerInvoice $invoice, float $amount, ?string $paymentNumber = null): Payment
    {
        if ($amount > (float) $invoice->open_amount) {
            AuditService::log('Payment Blocked', 'Customer Invoice', $invoice->invoice_number, "Payment of Rp" . number_format($amount, 0, ',', '.') . " exceeds open amount Rp" . number_format($invoice->open_amount, 0, ',', '.'));
            throw new Exception("Payment amount exceeds open invoice amount (Open: Rp" . number_format($invoice->open_amount, 0, ',', '.') . ")");
        }

        return DB::transaction(function () use ($invoice, $amount, $paymentNumber) {
            $payNum = $paymentNumber ?? ('PAY/' . date('Y') . '/' . str_pad(Payment::count() + 1, 5, '0', STR_PAD_LEFT));
            $jeNum = 'JE/PAY/' . substr($payNum, -5);

            $items = [
                [
                    'account_code' => '1010', // Bank
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'Receipt from ' . $invoice->customer_id . ' for ' . $invoice->invoice_number,
                ],
                [
                    'account_code' => '1100', // Accounts Receivable
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'Settle AR for ' . $invoice->invoice_number,
                ],
            ];

            self::createJournalEntry($jeNum, $invoice->invoice_number, 'Customer Payment', $payNum, $items);

            $payment = Payment::create([
                'payment_number' => $payNum,
                'payment_type' => 'customer_payment',
                'partner_id' => $invoice->customer_id,
                'partner_type' => 'customer',
                'invoice_id' => $invoice->invoice_number,
                'amount' => $amount,
                'payment_date' => date('Y-m-d'),
                'journal_account_code' => '1010',
                'status' => 'Posted',
                'journal_entry_number' => $jeNum,
            ]);

            $newPaid = (float) $invoice->paid_amount + $amount;
            $newOpen = (float) $invoice->open_amount - $amount;
            $newStatus = ($newOpen <= 0.01) ? 'Paid' : 'Posted';

            $invoice->update([
                'paid_amount' => $newPaid,
                'open_amount' => $newOpen,
                'status' => $newStatus,
            ]);

            AuditService::log('Register Payment', 'Customer Invoice', $invoice->invoice_number, "Payment Rp" . number_format($amount, 0, ',', '.') . " registered");
            return $payment;
        });
    }

    /**
     * Register Vendor Payment (DL-19, SR-23, F-25, T-37, T-38)
     */
    public static function registerVendorPayment(VendorBill $bill, float $amount, ?string $paymentNumber = null): Payment
    {
        if ($bill->status !== 'Posted') {
            throw new Exception("Vendor Bill must be Posted before registering payment.");
        }

        if ($amount > (float) $bill->open_amount) {
            AuditService::log('Vendor Payment Blocked', 'Vendor Bill', $bill->bill_number, "Payment of Rp" . number_format($amount, 0, ',', '.') . " exceeds open AP Rp" . number_format($bill->open_amount, 0, ',', '.'));
            throw new Exception("Payment amount exceeds open AP amount (Open: Rp" . number_format($bill->open_amount, 0, ',', '.') . ")");
        }

        return DB::transaction(function () use ($bill, $amount, $paymentNumber) {
            $payNum = $paymentNumber ?? ('PAY/V/' . date('Y') . '/' . str_pad(Payment::count() + 1, 5, '0', STR_PAD_LEFT));
            $jeNum = 'JE/PAYV/' . substr($payNum, -5);

            $items = [
                [
                    'account_code' => '2100', // Accounts Payable
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'AP settlement for ' . $bill->bill_number,
                ],
                [
                    'account_code' => '1010', // Bank
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'Payment to ' . $bill->vendor_id,
                ],
            ];

            self::createJournalEntry($jeNum, $bill->bill_number, 'Vendor Payment', $payNum, $items);

            $payment = Payment::create([
                'payment_number' => $payNum,
                'payment_type' => 'vendor_payment',
                'partner_id' => $bill->vendor_id,
                'partner_type' => 'vendor',
                'bill_id' => $bill->bill_number,
                'amount' => $amount,
                'payment_date' => date('Y-m-d'),
                'journal_account_code' => '1010',
                'status' => 'Posted',
                'journal_entry_number' => $jeNum,
            ]);

            $newPaid = (float) $bill->paid_amount + $amount;
            $newOpen = (float) $bill->open_amount - $amount;
            $newStatus = ($newOpen <= 0.01) ? 'Paid' : 'Posted';

            $bill->update([
                'paid_amount' => $newPaid,
                'open_amount' => $newOpen,
                'status' => $newStatus,
            ]);

            AuditService::log('Register Payment', 'Vendor Bill', $bill->bill_number, "Vendor Payment Rp" . number_format($amount, 0, ',', '.') . " registered");
            return $payment;
        });
    }
}
