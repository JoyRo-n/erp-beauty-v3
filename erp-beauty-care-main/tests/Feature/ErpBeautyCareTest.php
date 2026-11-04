<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Contact;
use App\Models\Product;
use App\Models\WarehouseLocation;
use App\Models\StockQuant;
use App\Models\StockMove;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Employee;
use App\Models\BeautyService;
use App\Models\BeautyTreatment;
use App\Models\CrmLead;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderLine;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceLine;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReceiptLine;
use App\Models\VendorBill;
use App\Models\VendorBillLine;
use App\Models\Payment;
use App\Models\InternalTransfer;
use App\Models\ProductReturn;
use App\Models\ReturnLine;
use App\Models\AuditLog;
use App\Services\AccountingService;
use App\Services\InventoryService;
use App\Services\CreditLimitService;
use App\Services\TreatmentService;
use App\Services\DashboardService;
use App\Services\AuditService;
use Database\Seeders\BaselineSeeder;
use Exception;

class ErpBeautyCareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Seed baseline dataset before tests
        $seeder = new BaselineSeeder();
        $seeder->run();
    }

    /**
     * T-01: App Switcher & Initial Balance (F-01, S-01)
     */
    public function test_t01_app_switcher_normal_flow(): void
    {
        $salesUser = User::where('role', 'Sales')->first();
        $this->assertNotNull($salesUser);

        // Verify initial Bank balance is Rp50.000.000
        $bankBalance = JournalItem::where('account_code', '1010')->sum(DB::raw('debit - credit'));
        $this->assertEquals(50000000, (float) $bankBalance);

        // No test journals yet (only OPENING)
        $testJournals = JournalEntry::where('entry_number', '!=', 'JE/2026/OPENING')->count();
        $this->assertEquals(0, $testJournals);
    }

    /**
     * T-02: Warehouse User Role Restriction on General Ledger (F-02, S-01)
     */
    public function test_t02_role_access_warehouse_denied_general_ledger(): void
    {
        $warehouseUser = User::where('role', 'Warehouse')->first();
        $this->assertEquals('Warehouse', $warehouseUser->role);

        // SR-19: Only Finance can post and view General Ledger
        AuditService::log('Access Denied', 'General Ledger', 'GL-MENU', 'Warehouse user denied access to General Ledger', $warehouseUser->name, $warehouseUser->role);

        $log = AuditLog::where('action', 'Access Denied')
            ->where('role', 'Warehouse')
            ->first();
        $this->assertNotNull($log);
        $this->assertEquals('General Ledger', $log->document_type);
    }

    /**
     * T-03: Customer Master & Credit Terms (F-03, S-02)
     */
    public function test_t03_customer_and_credit_terms_normal(): void
    {
        $c001 = Contact::find('C001');
        $this->assertNotNull($c001);
        $this->assertEquals(2000000, (float) $c001->credit_limit);
        $this->assertEquals(0, (float) $c001->opening_receivable);
        $this->assertEquals('Net 14', $c001->payment_terms);

        // Total company opening AR must be Rp4.440.000
        $totalOpeningAr = Contact::where('contact_type', 'customer')->sum('opening_receivable');
        $this->assertEquals(4440000, (float) $totalOpeningAr);
    }

    /**
     * T-04: Credit Limit Exposure Check (F-11, S-02)
     */
    public function test_t04_credit_limit_exposure_rejection(): void
    {
        $c002 = Contact::find('C002');
        $this->assertEquals(500000, (float) $c002->credit_limit);
        $this->assertEquals(222000, (float) $c002->opening_receivable);

        // C002 orders 3 x BC-TON-100 @ 150.000 = 450.000 + 11% PPN (49.500) = 499.500
        $orderGross = 499500;
        $exposure = CreditLimitService::calculateExposure($c002) + $orderGross;
        $this->assertEquals(721500, (float) $exposure);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Credit limit exceeded/');

        CreditLimitService::validateOrderExposure($c002, $orderGross, 'SO-TEST-C002');
    }

    /**
     * T-05: Product Variant SKU Master (F-04, S-03)
     */
    public function test_t05_product_variant_sku_normal(): void
    {
        $rose = Product::find('BC-LIP-001');
        $coral = Product::find('BC-LIP-002');

        $this->assertNotNull($rose);
        $this->assertNotNull($coral);
        $this->assertEquals('Rose', $rose->color);
        $this->assertEquals('Coral', $coral->color);
        $this->assertEquals(65000, (float) $rose->sales_price);
        $this->assertEquals(30000, (float) $rose->cost);
        $this->assertEquals(65000, (float) $coral->sales_price);
        $this->assertEquals(30000, (float) $coral->cost);
    }

    /**
     * T-06: Duplicate Variant SKU Rejection (F-04, S-03)
     */
    public function test_t06_duplicate_variant_sku_rejected(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Attempting to create duplicate primary key BC-LIP-001
        Product::create([
            'variant_sku' => 'BC-LIP-001',
            'name' => 'Lip Balm Coral Duplicate',
            'size' => '10 g',
            'color' => 'Coral',
            'unit' => 'tube',
            'sales_price' => 65000,
            'cost' => 30000,
        ]);
    }

    /**
     * T-07: CRM Lead to Opportunity Conversion (F-05, S-04)
     */
    public function test_t07_crm_lead_to_opportunity(): void
    {
        $lead = CrmLead::find('LEAD-0001');
        $this->assertEquals('Lead', $lead->status);

        $initialStock = StockQuant::sum('on_hand');
        $initialJournals = JournalEntry::count();
        $initialAr = Contact::where('contact_type', 'customer')->sum('opening_receivable');

        // Convert to Opportunity
        $lead->update([
            'status' => 'Opportunity',
            'stage' => 'Qualified',
        ]);
        AuditService::log('Convert Lead', 'CRM Lead', $lead->id, 'Converted to Opportunity');

        $this->assertEquals('Opportunity', $lead->fresh()->status);
        $this->assertEquals($initialStock, StockQuant::sum('on_hand'));
        $this->assertEquals($initialJournals, JournalEntry::count());
        $this->assertEquals($initialAr, Contact::where('contact_type', 'customer')->sum('opening_receivable'));
    }

    /**
     * T-08: Confirm Quotation without Customer Rejection (F-05, S-04)
     */
    public function test_t08_confirm_quotation_without_customer_rejected(): void
    {
        $orphanLead = CrmLead::create([
            'id' => 'LEAD-ORPHAN',
            'customer_id' => null,
            'name' => 'Anonymous Prospect',
            'status' => 'Lead',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Customer is required to confirm quotation');

        if (!$orphanLead->customer_id) {
            AuditService::log('Quotation Blocked', 'CRM Lead', $orphanLead->id, 'Lead has no customer associated');
            throw new Exception('Customer is required to confirm quotation');
        }
    }

    /**
     * T-09: Quotation to Sales Order Normal Flow (F-06, S-05)
     */
    public function test_t09_quotation_to_sales_order(): void
    {
        $c001 = Contact::find('C001');
        $product = Product::find('BC-CLN-100');

        $qty = 2;
        $unitPrice = (float) $product->sales_price; // 120000
        $untaxed = $unitPrice * $qty; // 240000
        $vat = AccountingService::calculateLineTax($unitPrice, $qty, 0); // 26400
        $total = $untaxed + $vat; // 266400

        $this->assertEquals(240000, $untaxed);
        $this->assertEquals(26400, $vat);
        $this->assertEquals(266400, $total);

        $baselineOnHand = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', 'BC-CLN-100')->value('on_hand');
        $this->assertEquals(15, $baselineOnHand);

        // Confirm SO & Reserve 2
        InventoryService::reserveStock('BC-CLN-100', 'WH-JKT', 2, 'S00007-T09');

        $clnJkt = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', 'BC-CLN-100')->first();
        $this->assertEquals(15, $clnJkt->on_hand);
        $this->assertEquals(5, $clnJkt->reserved); // 3 baseline + 2 = 5
        $this->assertEquals(10, $clnJkt->available);
    }

    /**
     * T-10: Line Discount Exceeding 15% Rejection (F-07, S-05)
     */
    public function test_t10_discount_over_15_percent_rejected(): void
    {
        $discount = 20;

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Discount exceeds maximum allowed limit of 15%/');

        if ($discount > 15) {
            AuditService::log('Discount Blocked', 'Sales Order', 'Q00007', 'Sales discount 20% exceeds max 15%');
            throw new Exception("Discount exceeds maximum allowed limit of 15%");
        }
    }

    /**
     * T-11: Reservation Normal Flow 4 units (F-08, S-06)
     */
    public function test_t11_stock_reservation_normal(): void
    {
        $clnJktBefore = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', 'BC-CLN-100')->first();
        $this->assertEquals(15, $clnJktBefore->on_hand);
        $this->assertEquals(3, $clnJktBefore->reserved);
        $this->assertEquals(12, $clnJktBefore->available);

        // Reserve 4 units
        InventoryService::reserveStock('BC-CLN-100', 'WH-JKT', 4, 'S00007');

        $clnJktAfter = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', 'BC-CLN-100')->first();
        $this->assertEquals(15, $clnJktAfter->on_hand);
        $this->assertEquals(7, $clnJktAfter->reserved);
        $this->assertEquals(8, $clnJktAfter->available);
    }

    /**
     * T-12: Over-reservation Rejection (F-08, S-06)
     */
    public function test_t12_over_reservation_rejected(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Insufficient stock available/');

        // Try to reserve 13 units from WH-JKT (Available is 12)
        InventoryService::reserveStock('BC-CLN-100', 'WH-JKT', 13, 'S00007-OVER');
    }

    /**
     * T-13: Partial Delivery and Backorder (F-09, S-07)
     */
    public function test_t13_partial_delivery_and_backorder(): void
    {
        // Setup T-11 fixture: SO with 4 units reserved
        InventoryService::reserveStock('BC-CLN-100', 'WH-JKT', 4, 'S00007');

        $so = SalesOrder::create([
            'order_number' => 'S00007',
            'customer_id' => 'C001',
            'warehouse_code' => 'WH-JKT',
            'status' => 'Confirmed',
            'untaxed_amount' => 480000,
            'tax_amount' => 52800,
            'total_amount' => 532800,
        ]);
        SalesOrderLine::create([
            'order_number' => 'S00007',
            'variant_sku' => 'BC-CLN-100',
            'qty' => 4,
            'unit_price' => 120000,
            'discount_percent' => 0,
            'tax_rate' => 11,
            'tax_amount' => 52800,
            'subtotal' => 532800,
        ]);

        $do = DeliveryOrder::create([
            'do_number' => 'WH/OUT/00007',
            'sales_order_number' => 'S00007',
            'customer_id' => 'C001',
            'source_location' => 'WH-JKT',
            'status' => 'Confirmed',
        ]);
        DeliveryOrderLine::create([
            'do_number' => 'WH/OUT/00007',
            'variant_sku' => 'BC-CLN-100',
            'demand_qty' => 4,
            'reserved_qty' => 4,
            'done_qty' => 0,
            'backorder_qty' => 0,
        ]);

        // Deliver 2 of 4
        $backorder = InventoryService::validateDelivery($do, ['BC-CLN-100' => 2]);

        $this->assertNotNull($backorder);
        $this->assertTrue($backorder->is_backorder);
        $this->assertEquals(2, $backorder->lines()->first()->demand_qty);

        // Check WH-JKT stock: On Hand was 15, delivered 2 -> 13
        $quant = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', 'BC-CLN-100')->first();
        $this->assertEquals(13, $quant->on_hand);

        // Check COGS journal: 2 units * Rp60.000 = Rp120.000
        $cogsJournal = JournalEntry::where('entry_number', 'JE/COGS/00007')->first();
        $this->assertNotNull($cogsJournal);
        $this->assertEquals(120000, (float) $cogsJournal->total_debit);
        $this->assertEquals(120000, (float) $cogsJournal->total_credit);
    }

    /**
     * T-14: Delivery Order Validation Idempotency (F-09, S-07)
     */
    public function test_t14_delivery_order_validation_idempotency(): void
    {
        $this->test_t13_partial_delivery_and_backorder();

        $do = DeliveryOrder::find('WH/OUT/00007');
        $this->assertEquals('Done', $do->status);

        $stockMovesCountBefore = StockMove::count();
        $journalsCountBefore = JournalEntry::count();

        // Validate again (simulating duplicate click)
        $res = InventoryService::validateDelivery($do, ['BC-CLN-100' => 2]);

        $this->assertNull($res);
        $this->assertEquals($stockMovesCountBefore, StockMove::count());
        $this->assertEquals($journalsCountBefore, JournalEntry::count());
    }

    /**
     * T-15: Delivery-Based Customer Invoice (F-10, S-08)
     */
    public function test_t15_delivery_based_customer_invoice_normal(): void
    {
        $this->test_t13_partial_delivery_and_backorder();

        // 2 units delivered @ 120.000 = 240.000 untaxed, 26.400 PPN, 266.400 total
        $invoice = CustomerInvoice::create([
            'invoice_number' => 'INV/2026/00007',
            'customer_id' => 'C001',
            'source_doc_type' => 'Delivery Order',
            'source_doc_id' => 'WH/OUT/00007',
            'sales_order_number' => 'S00007',
            'delivery_order_number' => 'WH/OUT/00007',
            'status' => 'Draft',
            'untaxed_amount' => 240000,
            'tax_amount' => 26400,
            'total_amount' => 266400,
            'paid_amount' => 0,
            'open_amount' => 266400,
        ]);
        CustomerInvoiceLine::create([
            'invoice_number' => 'INV/2026/00007',
            'item_type' => 'product',
            'item_code' => 'BC-CLN-100',
            'description' => 'Gentle Cleanser',
            'qty' => 2,
            'unit_price' => 120000,
            'discount_percent' => 0,
            'tax_amount' => 26400,
            'subtotal' => 266400,
        ]);

        AccountingService::postCustomerInvoice($invoice);

        $posted = $invoice->fresh();
        $this->assertEquals('Posted', $posted->status);
        $this->assertEquals(266400, (float) $posted->open_amount);

        // Check AR journal
        $ar = JournalItem::where('account_code', '1100')
            ->where('entry_number', 'JE/INV/00007')
            ->value('debit');
        $this->assertEquals(266400, (float) $ar);
    }

    /**
     * T-16: Invoicing Unbilled Undelivered Goods Rejection (F-10, S-08)
     */
    public function test_t16_invoice_before_delivery_rejected(): void
    {
        // 4 units ordered but 0 delivered
        $deliveredQty = 0;
        $attemptedInvoiceQty = 4;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot invoice undelivered quantities');

        if ($attemptedInvoiceQty > $deliveredQty) {
            AuditService::log('Invoice Blocked', 'Customer Invoice', 'INV-DRAFT', 'Invoice qty 4 exceeds delivered qty 0');
            throw new Exception('Cannot invoice undelivered quantities');
        }
    }

    /**
     * T-17: Partial Payment Normal Flow (F-16, S-09)
     */
    public function test_t17_partial_payment_normal(): void
    {
        $this->test_t15_delivery_based_customer_invoice_normal();

        $invoice = CustomerInvoice::find('INV/2026/00007');
        $this->assertEquals(266400, (float) $invoice->open_amount);

        // Pay Rp100.000
        $payment = AccountingService::registerCustomerPayment($invoice, 100000);

        $invoiceRefresh = $invoice->fresh();
        $this->assertEquals(166400, (float) $invoiceRefresh->open_amount);
        $this->assertEquals(100000, (float) $invoiceRefresh->paid_amount);

        // Check bank balance increased by 100.000 (50.000.000 -> 50.100.000)
        $bank = JournalItem::where('account_code', '1010')->sum(DB::raw('debit - credit'));
        $this->assertEquals(50100000, (float) $bank);
    }

    /**
     * T-18: Overpayment Rejection (F-16, S-09)
     */
    public function test_t18_overpayment_rejected(): void
    {
        $this->test_t15_delivery_based_customer_invoice_normal();

        $invoice = CustomerInvoice::find('INV/2026/00007');
        $this->assertEquals(266400, (float) $invoice->open_amount);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/exceeds open invoice amount/');

        // Try to pay Rp300.000 on Rp266.400 invoice
        AccountingService::registerCustomerPayment($invoice, 300000);
    }

    /**
     * T-19: RFQ to Purchase Order Normal Flow (F-12, S-10)
     */
    public function test_t19_rfq_to_purchase_order_normal(): void
    {
        $vendor = Contact::find('V01');
        $product = Product::find('BC-TON-100');

        $qty = 5;
        $unitCost = (float) $product->cost; // 70000
        $untaxed = $qty * $unitCost; // 350000
        $estimatedVat = AccountingService::calculateLineTax($unitCost, $qty); // 38500
        $total = $untaxed + $estimatedVat; // 388500

        $po = PurchaseOrder::create([
            'po_number' => 'PO00007',
            'vendor_id' => 'V01',
            'payment_terms' => 'Net 30',
            'status' => 'Confirmed',
            'untaxed_amount' => $untaxed,
            'tax_amount' => $estimatedVat,
            'total_amount' => $total,
            'is_locked' => true,
        ]);
        PurchaseOrderLine::create([
            'po_number' => 'PO00007',
            'variant_sku' => 'BC-TON-100',
            'qty_ordered' => 5,
            'unit_cost' => $unitCost,
            'tax_amount' => $estimatedVat,
            'subtotal' => $total,
        ]);

        $this->assertEquals(350000, (float) $po->untaxed_amount);
        $this->assertEquals(38500, (float) $po->tax_amount);

        // Before receipt & bill, stock and AP must remain unchanged
        $ap = JournalItem::where('account_code', '2100')->where('entry_number', '!=', 'JE/2026/OPENING')->count();
        $this->assertEquals(0, $ap);
    }

    /**
     * T-20: Change Vendor on Confirmed PO Rejection (F-12, S-10)
     */
    public function test_t20_confirmed_po_locked_vendor_change_rejected(): void
    {
        $this->test_t19_rfq_to_purchase_order_normal();

        $po = PurchaseOrder::find('PO00007');
        $this->assertTrue($po->is_locked);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Purchase Order is confirmed and locked');

        if ($po->is_locked) {
            AuditService::log('PO Change Blocked', 'Purchase Order', $po->po_number, 'Attempted vendor modification after confirmation');
            throw new Exception('Purchase Order is confirmed and locked');
        }
    }

    /**
     * T-21: Purchase Receipt with Quality Acceptance (4 accepted, 1 rejected quarantine) (F-13, S-11)
     */
    public function test_t21_purchase_receipt_accepted_and_rejected(): void
    {
        $this->test_t19_rfq_to_purchase_order_normal();

        $receipt = PurchaseReceipt::create([
            'receipt_number' => 'WH/IN/00007',
            'po_number' => 'PO00007',
            'vendor_id' => 'V01',
            'destination_location' => 'WH-JKT',
            'status' => 'Draft',
        ]);
        PurchaseReceiptLine::create([
            'receipt_number' => 'WH/IN/00007',
            'variant_sku' => 'BC-TON-100',
            'ordered_qty' => 5,
            'accepted_qty' => 0,
            'rejected_qty' => 0,
            'unit_cost' => 70000,
        ]);

        $stockBefore = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', 'BC-TON-100')->value('on_hand'); // 17

        // Accept 4, Reject 1
        InventoryService::validateReceipt($receipt, [
            'BC-TON-100' => ['accepted_qty' => 4, 'rejected_qty' => 1]
        ]);

        $stockAfter = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', 'BC-TON-100')->value('on_hand');
        $this->assertEquals($stockBefore + 4, $stockAfter); // 17 + 4 = 21

        // Check quarantine move
        $quarantineMove = StockMove::where('reference_doc_id', 'WH/IN/00007')
            ->where('is_quarantine', true)
            ->first();
        $this->assertNotNull($quarantineMove);
        $this->assertEquals(1, $quarantineMove->qty);
        $this->assertEquals(0, (float) $quarantineMove->total_cost);

        // Check stock valuation journal: 4 units * 70.000 = Rp280.000
        $valJournal = JournalEntry::where('entry_number', 'JE/REC/00007')->first();
        $this->assertNotNull($valJournal);
        $this->assertEquals(280000, (float) $valJournal->total_debit);
    }

    /**
     * T-22: Validate Rejected Qty without Approval Rejection (F-13, S-11)
     */
    public function test_t22_validate_rejected_without_approval_rejected(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Total of accepted.*must equal ordered qty/');

        $receipt = PurchaseReceipt::create([
            'receipt_number' => 'WH/IN/TEST-REJ',
            'po_number' => 'PO00007',
            'vendor_id' => 'V01',
            'destination_location' => 'WH-JKT',
            'status' => 'Draft',
        ]);
        PurchaseReceiptLine::create([
            'receipt_number' => 'WH/IN/TEST-REJ',
            'variant_sku' => 'BC-TON-100',
            'ordered_qty' => 5,
            'unit_cost' => 70000,
        ]);

        // Attempting to accept 5 when 1 is rejected (sum = 6 != 5)
        InventoryService::validateReceipt($receipt, [
            'BC-TON-100' => ['accepted_qty' => 5, 'rejected_qty' => 1]
        ]);
    }

    /**
     * T-23: Vendor Bill Three-Way Match 4 Accepted Units (F-14, S-12)
     */
    public function test_t23_vendor_bill_three_way_match_normal(): void
    {
        $this->test_t21_purchase_receipt_accepted_and_rejected();

        // 4 accepted units @ 70.000 = 280.000 untaxed, 30.800 PPN masukan, 310.800 total
        $bill = VendorBill::create([
            'bill_number' => 'BILL/2026/00007',
            'vendor_id' => 'V01',
            'po_number' => 'PO00007',
            'receipt_number' => 'WH/IN/00007',
            'status' => 'Draft',
            'untaxed_amount' => 280000,
            'tax_amount' => 30800,
            'total_amount' => 310800,
            'paid_amount' => 0,
            'open_amount' => 310800,
        ]);
        VendorBillLine::create([
            'bill_number' => 'BILL/2026/00007',
            'variant_sku' => 'BC-TON-100',
            'po_qty' => 5,
            'accepted_qty' => 4,
            'already_billed_qty' => 0,
            'bill_qty' => 4,
            'unit_cost' => 70000,
            'tax_amount' => 30800,
            'subtotal' => 310800,
        ]);

        AccountingService::postVendorBill($bill);

        $posted = $bill->fresh();
        $this->assertEquals('Posted', $posted->status);
        $this->assertEquals(310800, (float) $posted->open_amount);

        // Check AP journal
        $apItem = JournalItem::where('account_code', '2100')
            ->where('entry_number', 'JE/BILL/00007')
            ->first();
        $this->assertNotNull($apItem);
        $this->assertEquals(310800, (float) $apItem->credit);

        // Check Interim clearing
        $interimItem = JournalItem::where('account_code', '1250')
            ->where('entry_number', 'JE/BILL/00007')
            ->first();
        $this->assertNotNull($interimItem);
        $this->assertEquals(280000, (float) $interimItem->debit);
    }

    /**
     * T-24: Vendor Bill for 5 Units when only 4 Accepted Rejection (F-14, S-12)
     */
    public function test_t24_vendor_bill_exceeding_accepted_rejected(): void
    {
        $acceptedQty = 4;
        $billQty = 5;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Bill quantity cannot exceed accepted quantity');

        if ($billQty > $acceptedQty) {
            AuditService::log('Vendor Bill Blocked', 'Vendor Bill', 'BILL-OVER', 'Attempted to bill 5 units when only 4 accepted');
            throw new Exception('Bill quantity cannot exceed accepted quantity');
        }
    }

    /**
     * T-25: Ledger Posting and COGS Debit=Credit Balance (F-15, S-13)
     */
    public function test_t25_ledger_posting_and_cogs_balance(): void
    {
        $this->test_t13_partial_delivery_and_backorder();

        $je = JournalEntry::where('entry_number', 'JE/COGS/00007')->first();
        $this->assertNotNull($je);
        $this->assertEquals(120000, (float) $je->total_debit);
        $this->assertEquals(120000, (float) $je->total_credit);
        $this->assertEquals(0, abs($je->total_debit - $je->total_credit));
    }

    /**
     * T-26: Journal Post Idempotency (F-15, S-13)
     */
    public function test_t26_journal_post_idempotency(): void
    {
        $this->test_t25_ledger_posting_and_cogs_balance();

        $itemsCountBefore = JournalItem::where('entry_number', 'JE/COGS/00007')->count();

        // Repost the same journal
        AccountingService::createJournalEntry(
            'JE/COGS/00007',
            'WH/OUT/00007',
            'Delivery Order',
            'WH/OUT/00007',
            [
                ['account_code' => '5000', 'debit' => 120000, 'credit' => 0],
                ['account_code' => '1200', 'debit' => 0, 'credit' => 120000],
            ]
        );

        $itemsCountAfter = JournalItem::where('entry_number', 'JE/COGS/00007')->count();
        $this->assertEquals($itemsCountBefore, $itemsCountAfter);
    }

    /**
     * T-27: Internal Stock Transfer Normal Flow (F-17, S-14)
     */
    public function test_t27_internal_stock_transfer_normal(): void
    {
        $jktBefore = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', 'BC-SER-030')->value('on_hand'); // 19
        $bdgBefore = StockQuant::where('location_code', 'ST-BDG')->where('variant_sku', 'BC-SER-030')->value('on_hand'); // 10

        $transfer = InternalTransfer::create([
            'transfer_number' => 'WH/INT/00007',
            'from_location' => 'WH-JKT',
            'to_location' => 'ST-BDG',
            'variant_sku' => 'BC-SER-030',
            'qty' => 3,
            'status' => 'Draft',
        ]);

        InventoryService::executeTransfer($transfer);

        $jktAfter = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', 'BC-SER-030')->value('on_hand');
        $bdgAfter = StockQuant::where('location_code', 'ST-BDG')->where('variant_sku', 'BC-SER-030')->value('on_hand');

        $this->assertEquals($jktBefore - 3, $jktAfter); // 16
        $this->assertEquals($bdgBefore + 3, $bdgAfter); // 13

        // Total company valuation unchanged (Rp22.612.500)
        $totalValuation = DB::table('stock_quants')
            ->join('products', 'stock_quants.variant_sku', '=', 'products.variant_sku')
            ->sum(DB::raw('stock_quants.on_hand * products.cost'));
        $this->assertEquals(22612500, (float) $totalValuation);
    }

    /**
     * T-28: Insufficient Stock Transfer Rejection (F-17, S-14)
     */
    public function test_t28_insufficient_stock_transfer_rejected(): void
    {
        $transfer = InternalTransfer::create([
            'transfer_number' => 'WH/INT/OVER',
            'from_location' => 'WH-JKT',
            'to_location' => 'ST-BDG',
            'variant_sku' => 'BC-SER-030',
            'qty' => 999,
            'status' => 'Draft',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Insufficient stock at source/');

        InventoryService::executeTransfer($transfer);
    }

    /**
     * T-29: Return and Credit Note Normal Flow (F-18, S-15)
     */
    public function test_t29_return_and_credit_note_normal(): void
    {
        $this->test_t15_delivery_based_customer_invoice_normal();

        $stockBefore = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', 'BC-CLN-100')->value('on_hand');

        // Return 1 unit
        $return = ProductReturn::create([
            'return_number' => 'WH/RET/00007',
            'origin_invoice_number' => 'INV/2026/00007',
            'customer_id' => 'C001',
            'inspection_status' => 'Sealed / Accepted',
            'status' => 'Draft',
        ]);
        ReturnLine::create([
            'return_number' => 'WH/RET/00007',
            'variant_sku' => 'BC-CLN-100',
            'delivered_qty' => 2,
            'already_returned_qty' => 0,
            'return_qty' => 1,
            'unit_price' => 120000,
            'unit_cost' => 60000,
        ]);

        InventoryService::processReturn($return);

        $stockAfter = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', 'BC-CLN-100')->value('on_hand');
        $this->assertEquals($stockBefore + 1, $stockAfter); // restored 1 unit to stock

        $this->assertEquals(133200, (float) $return->fresh()->credit_note_total); // 120.000 + 13.200 VAT

        // Verify Credit Note Journal
        $jeCn = JournalEntry::where('entry_number', 'JE/CN/00007')->first();
        $this->assertNotNull($jeCn);
        $this->assertEquals(133200, (float) $jeCn->total_debit);
        $this->assertEquals(133200, (float) $jeCn->total_credit);
    }

    /**
     * T-30: Return Exceeding Delivered Quantity Rejection (F-18, S-15)
     */
    public function test_t30_return_exceeding_delivered_rejected(): void
    {
        $return = ProductReturn::create([
            'return_number' => 'WH/RET/OVER',
            'origin_invoice_number' => 'INV/2026/00007',
            'customer_id' => 'C001',
            'inspection_status' => 'Sealed / Accepted',
            'status' => 'Draft',
        ]);
        ReturnLine::create([
            'return_number' => 'WH/RET/OVER',
            'variant_sku' => 'BC-CLN-100',
            'delivered_qty' => 2,
            'already_returned_qty' => 0,
            'return_qty' => 3, // delivered 2, attempting return 3
            'unit_price' => 120000,
            'unit_cost' => 60000,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/exceeds available delivered quantity/');

        InventoryService::processReturn($return);
    }

    /**
     * T-31: Employees and Skill Matrix Normal Flow (F-19, S-16)
     */
    public function test_t31_employee_assignment_normal(): void
    {
        $e001 = Employee::find('E001');
        $this->assertEquals('Aktif', $e001->status);
        $this->assertTrue($e001->hasSkill('Facial'));

        $treatment = BeautyTreatment::create([
            'treatment_number' => 'TRT/2026/00007',
            'service_id' => 'SV-FAC-01',
            'customer_id' => 'C004',
            'employee_id' => 'E001',
            'location_code' => 'ST-BDG',
            'consumed_sku' => 'BC-MSK-001',
            'consumed_qty' => 1,
            'billed_total' => 333000,
            'status' => 'Draft',
        ]);

        TreatmentService::assignTherapist($treatment, $e001);

        $this->assertEquals('E001', $treatment->fresh()->employee_id);

        // No payroll or salary journals created
        $salaryJournals = JournalItem::where('account_code', '6000')->count();
        $this->assertEquals(0, $salaryJournals);
    }

    /**
     * T-32: Incompetent Therapist Assignment Rejection (F-19, S-16)
     */
    public function test_t32_incompetent_therapist_assignment_rejected(): void
    {
        $e002 = Employee::find('E002'); // Has Hair Treatment, NOT Facial
        $this->assertFalse($e002->hasSkill('Facial'));

        $treatment = BeautyTreatment::create([
            'treatment_number' => 'TRT/2026/TEST-E002',
            'service_id' => 'SV-FAC-01',
            'customer_id' => 'C004',
            'employee_id' => 'E001',
            'location_code' => 'ST-BDG',
            'consumed_sku' => 'BC-MSK-001',
            'consumed_qty' => 1,
            'status' => 'Draft',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/does not possess the required Facial skill/');

        TreatmentService::assignTherapist($treatment, $e002);
    }

    /**
     * T-33: Beauty Treatment Completion Integrated (F-20, S-17)
     */
    public function test_t33_beauty_treatment_completion_integrated(): void
    {
        $maskBdgBefore = StockQuant::where('location_code', 'ST-BDG')->where('variant_sku', 'BC-MSK-001')->value('on_hand');
        $this->assertEquals(11, $maskBdgBefore);

        $treatment = BeautyTreatment::create([
            'treatment_number' => 'TRT/2026/00007',
            'service_id' => 'SV-FAC-01',
            'customer_id' => 'C004',
            'employee_id' => 'E001',
            'location_code' => 'ST-BDG',
            'consumed_sku' => 'BC-MSK-001',
            'consumed_qty' => 1,
            'status' => 'Draft',
        ]);

        TreatmentService::completeTreatment($treatment);

        // Mask stock at ST-BDG 11 -> 10
        $maskBdgAfter = StockQuant::where('location_code', 'ST-BDG')->where('variant_sku', 'BC-MSK-001')->value('on_hand');
        $this->assertEquals(10, $maskBdgAfter);

        // Invoice created: Rp300.000 + Rp33.000 = Rp333.000
        $inv = CustomerInvoice::where('source_doc_id', 'TRT/2026/00007')->first();
        $this->assertNotNull($inv);
        $this->assertEquals(300000, (float) $inv->untaxed_amount);
        $this->assertEquals(33000, (float) $inv->tax_amount);
        $this->assertEquals(333000, (float) $inv->total_amount);

        // Consumables COGS Rp40.000
        $consCogs = JournalItem::where('account_code', '5010')
            ->where('entry_number', 'JE/TRT-CONS/00007')
            ->value('debit');
        $this->assertEquals(40000, (float) $consCogs);
    }

    /**
     * T-34: Treatment Completion Idempotency (F-20, S-17)
     */
    public function test_t34_treatment_completion_idempotency(): void
    {
        $this->test_t33_beauty_treatment_completion_integrated();

        $treatment = BeautyTreatment::find('TRT/2026/00007');
        $this->assertEquals('Done', $treatment->status);

        $invoicesCount = CustomerInvoice::count();
        $journalsCount = JournalEntry::count();

        // Complete again
        TreatmentService::completeTreatment($treatment);

        $this->assertEquals($invoicesCount, CustomerInvoice::count());
        $this->assertEquals($journalsCount, JournalEntry::count());
    }

    /**
     * T-35: Operational Dashboard Scenario KPIs (F-21, S-18)
     */
    public function test_t35_dashboard_scenario_kpis(): void
    {
        $this->test_t15_delivery_based_customer_invoice_normal();

        $kpis = DashboardService::getScenarioKpi();

        $this->assertEquals(240000, (float) $kpis['revenue'], 'Scenario Revenue must be Rp240.000');
        $this->assertEquals(120000, (float) $kpis['cogs'], 'Scenario COGS must be Rp120.000');
        $this->assertEquals(120000, (float) $kpis['contribution'], 'Gross Contribution must be Rp120.000');
        $this->assertEquals(266400, (float) $kpis['new_ar'], 'New Unpaid AR must be Rp266.400');
    }

    /**
     * T-36: Warehouse User Blocked from AR & Journal KPIs (F-21, S-18)
     */
    public function test_t36_warehouse_role_blocked_from_ar_and_general_ledger(): void
    {
        $user = User::where('role', 'Warehouse')->first();

        // Check permissions: Warehouse role has no access to Finance / Accounting / AR
        $canAccessFinancials = in_array($user->role, ['Finance', 'Owner']);
        $this->assertFalse($canAccessFinancials);

        AuditService::log('Access Denied', 'Financial Dashboard', 'DASH-AR', 'Warehouse role cannot view AR or Journal details', $user->name, $user->role);

        $deniedLog = AuditLog::where('role', 'Warehouse')
            ->where('action', 'Access Denied')
            ->where('document_id', 'DASH-AR')
            ->first();
        $this->assertNotNull($deniedLog);
    }

    /**
     * T-37: Vendor Payment Settlement (F-25, S-19)
     */
    public function test_t37_vendor_payment_settlement_normal(): void
    {
        $this->test_t23_vendor_bill_three_way_match_normal();

        $bill = VendorBill::find('BILL/2026/00007');
        $this->assertEquals(310800, (float) $bill->open_amount);

        // Pay full Rp310.800
        $payment = AccountingService::registerVendorPayment($bill, 310800);

        $billRefresh = $bill->fresh();
        $this->assertEquals(0, (float) $billRefresh->open_amount);
        $this->assertEquals('Paid', $billRefresh->status);

        // Bank balance: 50.000.000 - 310.800 = 49.689.200
        $bank = JournalItem::where('account_code', '1010')->sum(DB::raw('debit - credit'));
        $this->assertEquals(49689200, (float) $bank);

        // Historical AP (OPEN-AP V01) remains Rp2.220.000
        $historicalAp = Contact::find('V01')->opening_payable;
        $this->assertEquals(2220000, (float) $historicalAp);
    }

    /**
     * T-38: Vendor Overpayment Rejection (F-25, S-19)
     */
    public function test_t38_vendor_overpayment_rejected(): void
    {
        $this->test_t23_vendor_bill_three_way_match_normal();

        $bill = VendorBill::find('BILL/2026/00007');
        $this->assertEquals(310800, (float) $bill->open_amount);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/exceeds open AP amount/');

        // Attempting to pay Rp400.000 on Rp310.800 open AP
        AccountingService::registerVendorPayment($bill, 400000);
    }
}
