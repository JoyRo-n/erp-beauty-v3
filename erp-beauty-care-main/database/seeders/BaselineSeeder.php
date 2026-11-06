<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Contact;
use App\Models\Product;
use App\Models\VendorPricelist;
use App\Models\WarehouseLocation;
use App\Models\StockQuant;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Employee;
use App\Models\BeautyService;
use App\Models\CrmLead;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;

class BaselineSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign keys for safe truncate
        DB::statement('PRAGMA foreign_keys = OFF;');

        $tables = [
            'users', 'log_notes', 'audit_logs', 'access_grants', 'app_notifications', 'return_lines', 'returns',
            'internal_transfers', 'payments', 'vendor_bill_lines', 'vendor_bills',
            'purchase_receipt_lines', 'purchase_receipts', 'purchase_order_lines',
            'purchase_orders', 'customer_invoice_lines', 'customer_invoices',
            'delivery_order_lines', 'delivery_orders', 'sales_order_lines',
            'sales_orders', 'crm_leads', 'beauty_treatments', 'beauty_services',
            'employees', 'journal_items', 'journal_entries', 'accounts',
            'stock_moves', 'stock_quants', 'warehouses_locations',
            'vendor_pricelists', 'products', 'contacts'
        ];

        if (\Illuminate\Support\Facades\Schema::hasTable('access_requests')) {
            $tables[] = 'access_requests';
        }

        foreach ($tables as $tbl) {
            DB::table($tbl)->truncate();
        }

        // 1. Users for Roles & Master Admin (SR-19)
        $users = [
            // Gmail accounts requested by user
            ['name' => 'Kevin Mahendra', 'email' => 'sales@gmail.com', 'role' => 'Sales', 'password' => 'sales123'],
            ['name' => 'Wahyu Saputra', 'email' => 'warehouse@gmail.com', 'role' => 'Warehouse', 'password' => 'warehouse123'],
            ['name' => 'Nanda Kusuma', 'email' => 'finance@gmail.com', 'role' => 'Finance', 'password' => 'finance123'],
            ['name' => 'Laras Kirana', 'email' => 'studio@gmail.com', 'role' => 'Studio', 'password' => 'studio123'],
            ['name' => 'Taufik Rahman', 'email' => 'purchasing@gmail.com', 'role' => 'Purchasing', 'password' => 'purchasing123'],
            ['name' => 'Ratih Widya', 'email' => 'owner@gmail.com', 'role' => 'Owner', 'password' => 'owner123'],
            ['name' => 'Master Administrator', 'email' => 'admin@gmail.com', 'role' => 'Admin', 'password' => 'admin123'],

            // Alias for @arunika.com backwards compatibility
            ['name' => 'Kevin Mahendra', 'email' => 'sales@arunika.com', 'role' => 'Sales', 'password' => 'sales123'],
            ['name' => 'Wahyu Saputra', 'email' => 'warehouse@arunika.com', 'role' => 'Warehouse', 'password' => 'warehouse123'],
            ['name' => 'Nanda Kusuma', 'email' => 'finance@arunika.com', 'role' => 'Finance', 'password' => 'finance123'],
            ['name' => 'Laras Kirana', 'email' => 'studio@arunika.com', 'role' => 'Studio', 'password' => 'studio123'],
            ['name' => 'Taufik Rahman', 'email' => 'purchasing@arunika.com', 'role' => 'Purchasing', 'password' => 'purchasing123'],
            ['name' => 'Ratih Widya', 'email' => 'owner@arunika.com', 'role' => 'Owner', 'password' => 'owner123'],
            ['name' => 'Master Administrator', 'email' => 'admin@arunika.com', 'role' => 'Admin', 'password' => 'admin123'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make($u['password']),
                    'role' => $u['role'],
                ]
            );
        }

        // 2. Contacts (Customers)
        $customers = [
            ['id' => 'C001', 'name' => 'Ayu Prameswari', 'type' => 'Perorangan', 'contact_type' => 'customer', 'city' => 'Jakarta', 'credit_limit' => 2000000, 'payment_terms' => 'Net 14', 'opening_receivable' => 0],
            ['id' => 'C002', 'name' => 'Nadira Putri', 'type' => 'Perorangan', 'contact_type' => 'customer', 'city' => 'Bandung', 'credit_limit' => 500000, 'payment_terms' => 'Net 14', 'opening_receivable' => 222000],
            ['id' => 'C003', 'name' => 'PT Anggun Sejahtera', 'type' => 'Perusahaan', 'contact_type' => 'customer', 'city' => 'Jakarta', 'credit_limit' => 8000000, 'payment_terms' => 'Net 30', 'opening_receivable' => 4218000],
            ['id' => 'C004', 'name' => 'Rani Kartika', 'type' => 'Perorangan', 'contact_type' => 'customer', 'city' => 'Depok', 'credit_limit' => 1200000, 'payment_terms' => 'Immediate', 'opening_receivable' => 0],
            ['id' => 'C005', 'name' => 'Dewi Natalia', 'type' => 'Perorangan', 'contact_type' => 'customer', 'city' => 'Bekasi', 'credit_limit' => 1000000, 'payment_terms' => 'Net 14', 'opening_receivable' => 0],
            ['id' => 'C006', 'name' => 'CV Cantik Bersama', 'type' => 'Perusahaan', 'contact_type' => 'customer', 'city' => 'Bandung', 'credit_limit' => 4000000, 'payment_terms' => 'Net 30', 'opening_receivable' => 0],
        ];
        foreach ($customers as $c) {
            Contact::create($c);
        }

        // 3. Contacts (Vendors)
        $vendors = [
            ['id' => 'V01', 'name' => 'PT Bumi Dermalab', 'type' => 'Perusahaan', 'contact_type' => 'vendor', 'city' => 'Jakarta', 'credit_limit' => 0, 'payment_terms' => 'Net 30', 'opening_payable' => 2220000],
            ['id' => 'V02', 'name' => 'CV Aura Kosmetika', 'type' => 'Perusahaan', 'contact_type' => 'vendor', 'city' => 'Bandung', 'credit_limit' => 0, 'payment_terms' => 'Net 14', 'opening_payable' => 0],
            ['id' => 'V03', 'name' => 'PT Proteksi Kulit', 'type' => 'Perusahaan', 'contact_type' => 'vendor', 'city' => 'Bogor', 'credit_limit' => 0, 'payment_terms' => 'Net 30', 'opening_payable' => 0],
            ['id' => 'V04', 'name' => 'CV Natura Indah', 'type' => 'Perusahaan', 'contact_type' => 'vendor', 'city' => 'Jakarta', 'credit_limit' => 0, 'payment_terms' => 'Net 14', 'opening_payable' => 0],
        ];
        foreach ($vendors as $v) {
            Contact::create($v);
        }

        // 4. Products
        $products = [
            ['variant_sku' => 'BC-CLN-100', 'name' => 'Gentle Cleanser', 'size' => '100 ml', 'color' => 'Bening', 'unit' => 'botol', 'sales_price' => 120000, 'cost' => 60000, 'default_vendor_id' => 'V01'],
            ['variant_sku' => 'BC-TON-100', 'name' => 'Hydrating Toner', 'size' => '100 ml', 'color' => 'Bening', 'unit' => 'botol', 'sales_price' => 150000, 'cost' => 70000, 'default_vendor_id' => 'V01'],
            ['variant_sku' => 'BC-SER-030', 'name' => 'Glow Serum', 'size' => '30 ml', 'color' => 'Amber', 'unit' => 'botol', 'sales_price' => 90000, 'cost' => 45000, 'default_vendor_id' => 'V02'],
            ['variant_sku' => 'BC-MSK-001', 'name' => 'Facial Treatment Mask', 'size' => '1 sachet', 'color' => 'Putih', 'unit' => 'sachet', 'sales_price' => 85000, 'cost' => 40000, 'default_vendor_id' => 'V02'],
            ['variant_sku' => 'BC-SCR-050', 'name' => 'Face Scrub', 'size' => '50 ml', 'color' => 'Putih', 'unit' => 'jar', 'sales_price' => 110000, 'cost' => 55000, 'default_vendor_id' => 'V01'],
            ['variant_sku' => 'BC-SPF-050', 'name' => 'Daily Sunscreen SPF50', 'size' => '50 ml', 'color' => 'Krem', 'unit' => 'tube', 'sales_price' => 130000, 'cost' => 65000, 'default_vendor_id' => 'V03'],
            ['variant_sku' => 'BC-CRM-050', 'name' => 'Day Cream', 'size' => '50 g', 'color' => 'Putih', 'unit' => 'jar', 'sales_price' => 165000, 'cost' => 82500, 'default_vendor_id' => 'V03'],
            ['variant_sku' => 'BC-CRM-051', 'name' => 'Night Cream', 'size' => '50 g', 'color' => 'Ungu', 'unit' => 'jar', 'sales_price' => 175000, 'cost' => 87500, 'default_vendor_id' => 'V03'],
            ['variant_sku' => 'BC-LIP-001', 'name' => 'Lip Balm', 'size' => '10 g', 'color' => 'Rose', 'unit' => 'tube', 'sales_price' => 65000, 'cost' => 30000, 'default_vendor_id' => 'V04'],
            ['variant_sku' => 'BC-LIP-002', 'name' => 'Lip Balm', 'size' => '10 g', 'color' => 'Coral', 'unit' => 'tube', 'sales_price' => 65000, 'cost' => 30000, 'default_vendor_id' => 'V04'],
            ['variant_sku' => 'BC-HAI-100', 'name' => 'Hair Serum', 'size' => '100 ml', 'color' => 'Amber', 'unit' => 'botol', 'sales_price' => 145000, 'cost' => 72000, 'default_vendor_id' => 'V02'],
            ['variant_sku' => 'BC-SHA-250', 'name' => 'Gentle Shampoo', 'size' => '250 ml', 'color' => 'Bening', 'unit' => 'botol', 'sales_price' => 95000, 'cost' => 47000, 'default_vendor_id' => 'V04'],
            ['variant_sku' => 'BC-CND-250', 'name' => 'Hair Conditioner', 'size' => '250 ml', 'color' => 'Putih', 'unit' => 'botol', 'sales_price' => 105000, 'cost' => 52000, 'default_vendor_id' => 'V04'],
            ['variant_sku' => 'BC-BDY-200', 'name' => 'Body Lotion', 'size' => '200 ml', 'color' => 'Putih', 'unit' => 'botol', 'sales_price' => 115000, 'cost' => 57000, 'default_vendor_id' => 'V01'],
            ['variant_sku' => 'BC-MST-100', 'name' => 'Body Mist', 'size' => '100 ml', 'color' => 'Pink', 'unit' => 'botol', 'sales_price' => 125000, 'cost' => 62000, 'default_vendor_id' => 'V04'],
        ];
        foreach ($products as $p) {
            Product::create($p);

            // Vendor Pricelist
            VendorPricelist::create([
                'vendor_id' => $p['default_vendor_id'],
                'variant_sku' => $p['variant_sku'],
                'min_qty' => 1,
                'purchase_price' => $p['cost'],
                'lead_time_days' => 7,
            ]);
        }

        // 5. Locations
        WarehouseLocation::create(['location_code' => 'WH-JKT', 'location_name' => 'Gudang pusat', 'city' => 'Jakarta', 'type' => 'Warehouse']);
        WarehouseLocation::create(['location_code' => 'ST-BDG', 'location_name' => 'Glow Studio / retail', 'city' => 'Bandung', 'type' => 'Store / studio']);

        // 6. Opening Stocks (396 units, Rp22.612.500)
        $stocks = [
            'BC-CLN-100' => ['WH-JKT' => 15, 'ST-BDG' => 8],
            'BC-TON-100' => ['WH-JKT' => 17, 'ST-BDG' => 9],
            'BC-SER-030' => ['WH-JKT' => 19, 'ST-BDG' => 10],
            'BC-MSK-001' => ['WH-JKT' => 15, 'ST-BDG' => 11],
            'BC-SCR-050' => ['WH-JKT' => 17, 'ST-BDG' => 8],
            'BC-SPF-050' => ['WH-JKT' => 19, 'ST-BDG' => 9],
            'BC-CRM-050' => ['WH-JKT' => 15, 'ST-BDG' => 10],
            'BC-CRM-051' => ['WH-JKT' => 17, 'ST-BDG' => 11],
            'BC-LIP-001' => ['WH-JKT' => 19, 'ST-BDG' => 8],
            'BC-LIP-002' => ['WH-JKT' => 15, 'ST-BDG' => 9],
            'BC-HAI-100' => ['WH-JKT' => 17, 'ST-BDG' => 10],
            'BC-SHA-250' => ['WH-JKT' => 19, 'ST-BDG' => 11],
            'BC-CND-250' => ['WH-JKT' => 15, 'ST-BDG' => 8],
            'BC-BDY-200' => ['WH-JKT' => 17, 'ST-BDG' => 9],
            'BC-MST-100' => ['WH-JKT' => 19, 'ST-BDG' => 10],
        ];

        foreach ($stocks as $sku => $locs) {
            foreach ($locs as $loc => $qty) {
                // S00006 reserves 3 units of BC-CLN-100 at WH-JKT
                $reserved = ($sku === 'BC-CLN-100' && $loc === 'WH-JKT') ? 3 : 0;
                StockQuant::create([
                    'location_code' => $loc,
                    'variant_sku' => $sku,
                    'on_hand' => $qty,
                    'reserved' => $reserved,
                ]);
            }
        }

        // 7. Chart of Accounts
        $coa = [
            ['code' => '1010', 'name' => 'Bank', 'type' => 'Asset'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'Asset'],
            ['code' => '1150', 'name' => 'Input VAT', 'type' => 'Asset'],
            ['code' => '1200', 'name' => 'Stock Valuation', 'type' => 'Asset'],
            ['code' => '1250', 'name' => 'Stock Interim Received', 'type' => 'Asset'],
            ['code' => '2100', 'name' => 'Accounts Payable', 'type' => 'Liability'],
            ['code' => '2150', 'name' => 'Output VAT', 'type' => 'Liability'],
            ['code' => '3100', 'name' => 'Opening Capital', 'type' => 'Equity'],
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'Income'],
            ['code' => '4010', 'name' => 'Service Revenue', 'type' => 'Income'],
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'Expense'],
            ['code' => '5010', 'name' => 'Treatment Consumables', 'type' => 'Expense'],
            ['code' => '6000', 'name' => 'Operating Expense', 'type' => 'Expense'],
            ['code' => '6100', 'name' => 'Bank Fees', 'type' => 'Expense'],
            ['code' => '9999', 'name' => 'Opening Balance Clearing', 'type' => 'Equity'],
        ];
        foreach ($coa as $acc) {
            Account::create($acc);
        }

        // 8. Opening Balances Journal Entry (Total: Rp77.052.500)
        $entry = JournalEntry::create([
            'entry_number' => 'JE/2026/OPENING',
            'date' => '2026-01-01',
            'reference' => 'Opening Balances',
            'source_doc_type' => 'Opening',
            'source_doc_id' => 'INIT',
            'status' => 'Posted',
            'total_debit' => 77052500,
            'total_credit' => 77052500,
            'posted_by' => 'System',
            'posted_at' => now(),
        ]);

        JournalItem::create(['entry_number' => $entry->entry_number, 'account_code' => '1010', 'debit' => 50000000, 'credit' => 0, 'description' => 'Opening Bank Balance']);
        JournalItem::create(['entry_number' => $entry->entry_number, 'account_code' => '1100', 'debit' => 4440000, 'credit' => 0, 'description' => 'Opening Accounts Receivable']);
        JournalItem::create(['entry_number' => $entry->entry_number, 'account_code' => '1200', 'debit' => 22612500, 'credit' => 0, 'description' => 'Opening Inventory Valuation (396 units)']);
        JournalItem::create(['entry_number' => $entry->entry_number, 'account_code' => '2100', 'debit' => 0, 'credit' => 2220000, 'description' => 'Opening Accounts Payable']);
        JournalItem::create(['entry_number' => $entry->entry_number, 'account_code' => '3100', 'debit' => 0, 'credit' => 74832500, 'description' => 'Opening Capital']);

        // 9. Employees
        Employee::create(['id' => 'E001', 'name' => 'Sinta Maharani', 'job_position' => 'Beauty Therapist', 'department' => 'Studio Bandung', 'skills' => ['Facial'], 'status' => 'Aktif']);
        Employee::create(['id' => 'E002', 'name' => 'Rizka Amalia', 'job_position' => 'Beauty Therapist', 'department' => 'Studio Bandung', 'skills' => ['Hair Treatment'], 'status' => 'Aktif']);
        Employee::create(['id' => 'E003', 'name' => 'Gita Lestari', 'job_position' => 'Supervisor', 'department' => 'Studio Bandung', 'skills' => ['Facial', 'Hair Treatment'], 'status' => 'Aktif']);
        Employee::create(['id' => 'E004', 'name' => 'Fajar Wijaya', 'job_position' => 'Stock Operator', 'department' => 'Gudang Jakarta', 'skills' => ['Stock Handling'], 'status' => 'Aktif']);

        // 10. Beauty Services
        BeautyService::create([
            'service_id' => 'SV-FAC-01',
            'name' => 'Glow Facial',
            'duration' => '60 menit',
            'sales_price' => 300000,
            'consumed_variant_sku' => 'BC-MSK-001',
            'qty_per_session' => 1,
            'required_skill' => 'Facial',
        ]);

        // 11. Existing Baseline Documents
        // LEAD-0001
        CrmLead::create([
            'id' => 'LEAD-0001',
            'customer_id' => 'C003',
            'name' => 'PT Anggun Sejahtera',
            'salesperson' => 'Kevin Mahendra',
            'stage' => 'New',
            'status' => 'Lead',
        ]);

        // S00006: C005 Dewi Natalia, Confirmed, 3 x BC-CLN-100
        $so = SalesOrder::create([
            'order_number' => 'S00006',
            'customer_id' => 'C005',
            'warehouse_code' => 'WH-JKT',
            'payment_terms' => 'Net 14',
            'status' => 'Confirmed',
            'untaxed_amount' => 360000,
            'tax_amount' => 39600,
            'total_amount' => 399600,
            'created_by' => 'Sales User',
            'confirmed_at' => now(),
        ]);
        SalesOrderLine::create([
            'order_number' => 'S00006',
            'variant_sku' => 'BC-CLN-100',
            'qty' => 3,
            'delivered_qty' => 0,
            'invoiced_qty' => 0,
            'unit_price' => 120000,
            'discount_percent' => 0,
            'tax_rate' => 11,
            'tax_amount' => 39600,
            'subtotal' => 399600,
        ]);

        // PO00006: V02 CV Aura Kosmetika, Confirmed, 8 x BC-SER-030
        $po = PurchaseOrder::create([
            'po_number' => 'PO00006',
            'vendor_id' => 'V02',
            'payment_terms' => 'Net 14',
            'status' => 'Confirmed',
            'untaxed_amount' => 360000,
            'tax_amount' => 39600,
            'total_amount' => 399600,
            'is_locked' => true,
            'confirmed_at' => now(),
        ]);
        PurchaseOrderLine::create([
            'po_number' => 'PO00006',
            'variant_sku' => 'BC-SER-030',
            'qty_ordered' => 8,
            'qty_received' => 0,
            'qty_billed' => 0,
            'unit_cost' => 45000,
            'tax_amount' => 39600,
            'subtotal' => 399600,
        ]);

        DB::statement('PRAGMA foreign_keys = ON;');
    }
}
