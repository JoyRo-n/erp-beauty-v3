<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;
use App\Models\Product;
use App\Models\VendorPricelist;
use App\Models\StockQuant;
use App\Models\StockMove;
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
use App\Models\LogNote;
use App\Services\AccountingService;

class HundredDatasetSeeder extends Seeder
{
    public function run(): void
    {
        // Start from the clean baseline dataset first
        $baseline = new BaselineSeeder();
        $baseline->run();

        DB::transaction(function () {
            $firstNames = [
                'Anindya', 'Bella', 'Citra', 'Dinda', 'Elsa', 'Farah', 'Gisella', 'Hana', 'Indira', 'Jasmine',
                'Kartika', 'Larasati', 'Maya', 'Nabila', 'Olivia', 'Putri', 'Qori', 'Raisa', 'Safira', 'Tiara',
                'Ulfa', 'Vania', 'Wulan', 'Yasmine', 'Zahra', 'intan', 'Amelia', 'Brigitta', 'Clarissa', 'Devina',
                'Evelyn', 'Febriani', 'Grace', 'Helena', 'Irene', 'Jessica', 'Keisya', 'Lydia', 'Meisya', 'Nadya',
                'Patricia', 'Queena', 'Renata', 'Shania', 'Talitha', 'Vanessa', 'Widya', 'Yolanda', 'Zaskia', 'Aurelia'
            ];
            $lastNames = [
                'Pratama', 'Kusuma', 'Wijaya', 'Saputri', 'Lestari', 'Maharani', 'Permata', 'Anggraini', 'Puspita', 'Rahmawati',
                'Santoso', 'Halim', 'Gunawan', 'Setiawan', 'Hidayat', 'Nugroho', 'Suryani', 'Wardhani', 'Utami', 'Handayani'
            ];
            $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Semarang', 'Yogyakarta', 'Depok', 'Bekasi', 'Tangerang', 'Bogor', 'Malang', 'Denpasar'];
            $terms = ['Net 14', 'Net 30', 'Immediate'];

            // 1. Expand Customers to 100 (C001..C006 exist -> add C007..C100)
            for ($i = 7; $i <= 100; $i++) {
                $cid = 'C' . str_pad($i, 3, '0', STR_PAD_LEFT);
                $isCorp = ($i % 4 === 0);
                $fn = $firstNames[($i - 7) % count($firstNames)];
                $ln = $lastNames[($i - 7) % count($lastNames)];
                $name = $isCorp
                    ? 'PT ' . ucfirst($fn) . ' ' . $ln . ' Estetika'
                    : ucfirst($fn) . ' ' . $ln;

                Contact::create([
                    'id' => $cid,
                    'name' => $name,
                    'type' => $isCorp ? 'Perusahaan' : 'Perorangan',
                    'contact_type' => 'customer',
                    'city' => $cities[$i % count($cities)],
                    'credit_limit' => $isCorp ? 15000000 : 5000000,
                    'payment_terms' => $terms[$i % count($terms)],
                    'opening_receivable' => 0,
                    'opening_payable' => 0,
                ]);
            }

            // 2. Expand Vendors to 30 (V01..V04 exist -> add V05..V30)
            $vendorTypes = ['Dermalab', 'Kosmetika', 'BioSkin', 'Natura', 'AromaTech', 'Botanica', 'ChemLab', 'PackIndo'];
            for ($i = 5; $i <= 30; $i++) {
                $vid = 'V' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $vName = ($i % 2 === 0 ? 'PT ' : 'CV ') . $lastNames[$i % count($lastNames)] . ' ' . $vendorTypes[$i % count($vendorTypes)];
                Contact::create([
                    'id' => $vid,
                    'name' => $vName,
                    'type' => 'Perusahaan',
                    'contact_type' => 'vendor',
                    'city' => $cities[$i % count($cities)],
                    'credit_limit' => 0,
                    'payment_terms' => ($i % 2 === 0) ? 'Net 30' : 'Net 14',
                    'opening_receivable' => 0,
                    'opening_payable' => 0,
                ]);
            }

            // 3. Expand Products to 30 unique SKUs (15 exist -> add 15 unique products)
            $extraProducts = [
                ['variant_sku' => 'BC-NIA-030', 'name' => 'Niacinamide 10% Brightening Serum', 'size' => '30 ml', 'color' => 'Bening', 'unit' => 'botol', 'sales_price' => 135000, 'cost' => 65000, 'default_vendor_id' => 'V01'],
                ['variant_sku' => 'BC-CER-050', 'name' => 'Ceramide Skin Barrier Moisturizer', 'size' => '50 g', 'color' => 'Putih', 'unit' => 'jar', 'sales_price' => 155000, 'cost' => 75000, 'default_vendor_id' => 'V02'],
                ['variant_sku' => 'BC-AHA-030', 'name' => 'AHA BHA PHA Peeling Solution', 'size' => '30 ml', 'color' => 'Merah', 'unit' => 'botol', 'sales_price' => 140000, 'cost' => 68000, 'default_vendor_id' => 'V03'],
                ['variant_sku' => 'BC-CEN-100', 'name' => 'Centella Asiatica Soothing Gel', 'size' => '100 ml', 'color' => 'Hijau', 'unit' => 'tube', 'sales_price' => 98000, 'cost' => 45000, 'default_vendor_id' => 'V04'],
                ['variant_sku' => 'BC-RET-030', 'name' => 'Retinol Youth Renewal Essence', 'size' => '30 ml', 'color' => 'Amber', 'unit' => 'botol', 'sales_price' => 185000, 'cost' => 90000, 'default_vendor_id' => 'V05'],
                ['variant_sku' => 'BC-HYA-100', 'name' => 'Hyaluronic Acid Hydrating Toner Mist', 'size' => '100 ml', 'color' => 'Biru', 'unit' => 'botol', 'sales_price' => 110000, 'cost' => 50000, 'default_vendor_id' => 'V06'],
                ['variant_sku' => 'BC-COL-001', 'name' => 'Collagen Firming Bio-Cellulose Mask', 'size' => '1 sachet', 'color' => 'Pink', 'unit' => 'sachet', 'sales_price' => 85000, 'cost' => 40000, 'default_vendor_id' => 'V07'],
                ['variant_sku' => 'BC-VLT-010', 'name' => 'Velvet Matte Lip Tint Berry', 'size' => '10 g', 'color' => 'Berry', 'unit' => 'tube', 'sales_price' => 75000, 'cost' => 35000, 'default_vendor_id' => 'V08'],
                ['variant_sku' => 'BC-MIC-200', 'name' => 'Rosewater Micellar Cleansing Water', 'size' => '200 ml', 'color' => 'Rose', 'unit' => 'botol', 'sales_price' => 115000, 'cost' => 55000, 'default_vendor_id' => 'V09'],
                ['variant_sku' => 'BC-UVT-050', 'name' => 'UV Shield Tone-Up Sunscreen SPF50+', 'size' => '50 ml', 'color' => 'Peach', 'unit' => 'tube', 'sales_price' => 145000, 'cost' => 70000, 'default_vendor_id' => 'V10'],
                ['variant_sku' => 'BC-EYE-015', 'name' => 'Peptide Firming Eye Cream', 'size' => '15 ml', 'color' => 'Putih', 'unit' => 'tube', 'sales_price' => 160000, 'cost' => 78000, 'default_vendor_id' => 'V11'],
                ['variant_sku' => 'BC-CLY-060', 'name' => 'Kaolin Pore Purifying Clay Mask', 'size' => '60 g', 'color' => 'Abu', 'unit' => 'jar', 'sales_price' => 125000, 'cost' => 60000, 'default_vendor_id' => 'V12'],
                ['variant_sku' => 'BC-OIL-030', 'name' => 'Squalane & Rosehip Facial Oil', 'size' => '30 ml', 'color' => 'Emas', 'unit' => 'botol', 'sales_price' => 170000, 'cost' => 82000, 'default_vendor_id' => 'V13'],
                ['variant_sku' => 'BC-BSH-300', 'name' => 'Chamomile Calming Body Wash', 'size' => '300 ml', 'color' => 'Kuning', 'unit' => 'botol', 'sales_price' => 105000, 'cost' => 50000, 'default_vendor_id' => 'V14'],
                ['variant_sku' => 'BC-HMK-200', 'name' => 'Argan Keratin Intensive Hair Mask', 'size' => '200 g', 'color' => 'Krem', 'unit' => 'jar', 'sales_price' => 150000, 'cost' => 72000, 'default_vendor_id' => 'V15'],
            ];

            foreach ($extraProducts as $idx => $ep) {
                Product::updateOrCreate(['variant_sku' => $ep['variant_sku']], $ep);

                VendorPricelist::create([
                    'vendor_id' => $ep['default_vendor_id'],
                    'variant_sku' => $ep['variant_sku'],
                    'min_qty' => 1,
                    'purchase_price' => $ep['cost'],
                    'lead_time_days' => 5 + ($idx % 5),
                ]);

                StockQuant::updateOrCreate(
                    ['location_code' => 'WH-JKT', 'variant_sku' => $ep['variant_sku']],
                    ['on_hand' => 25 + ($idx * 2), 'reserved' => 0]
                );

                StockQuant::updateOrCreate(
                    ['location_code' => 'ST-BDG', 'variant_sku' => $ep['variant_sku']],
                    ['on_hand' => 12 + ($idx % 8), 'reserved' => 0]
                );
            }

            // Boost base 15 products stock so 100 orders/deliveries/treatments have plenty of stock
            $baseSkus = [
                'BC-CLN-100', 'BC-TON-100', 'BC-SER-030', 'BC-MSK-001', 'BC-SCR-050',
                'BC-SPF-050', 'BC-CRM-050', 'BC-CRM-051', 'BC-LIP-001', 'BC-LIP-002',
                'BC-HAI-100', 'BC-SHA-250', 'BC-CND-250', 'BC-BDY-200', 'BC-MST-100'
            ];

            // 4. Expand Employees to 50 (E001..E004 exist -> add E005..E050)
            $positions = ['Beauty Therapist', 'Senior Aesthetician', 'Hair Specialist', 'Stock Operator', 'Sales Advisor', 'QC Inspector'];
            $depts = ['Studio Bandung', 'Gudang Jakarta', 'Divisi Penjualan', 'Quality Control'];
            $skillSets = [
                ['Facial'],
                ['Hair Treatment'],
                ['Facial', 'Hair Treatment'],
                ['Stock Handling'],
                ['Facial', 'Skin Analysis'],
            ];

            $streets = [
                'Jl. Braga No. 25, Sumur Bandung, Kota Bandung',
                'Jl. Sudirman Kav. 48, Setiabudi, Jakarta Selatan',
                'Jl. Riau (LLRE Martadinata) No. 112, Bandung Wetan, Kota Bandung',
                'Jl. Senopati No. 64, Kebayoran Baru, Jakarta Selatan',
                'Jl. Pasir Kaliki No. 91, Cicendo, Kota Bandung',
                'Jl. Kelapa Gading Boulevard Blok QA-3, Jakarta Utara',
                'Jl. Cihampelas No. 150, Cipaganti, Kota Bandung',
                'Jl. Bintaro Utama Sektor 5 No. 19, Tangerang Selatan',
                'Jl. Gatot Subroto No. 77, Lengkong, Kota Bandung',
                'Jl. Puri Indah Raya Blok U-12, Kembangan, Jakarta Barat',
            ];

            for ($i = 5; $i <= 50; $i++) {
                $eid = 'E' . str_pad($i, 3, '0', STR_PAD_LEFT);
                $fn = $firstNames[$i % count($firstNames)];
                $ln = $lastNames[($i * 3) % count($lastNames)];
                $birthYear = 1992 + ($i % 10);
                $birthMonth = str_pad((($i % 12) + 1), 2, '0', STR_PAD_LEFT);
                $birthDay = str_pad((($i * 2) % 28) + 1, 2, '0', STR_PAD_LEFT);
                $phoneNum = '+62 812-' . str_pad(3100 + ($i * 17), 4, '0', STR_PAD_LEFT) . '-' . str_pad(1000 + ($i * 29), 4, '0', STR_PAD_LEFT);
                $addr = $streets[$i % count($streets)];

                Employee::create([
                    'id' => $eid,
                    'name' => ucfirst($fn) . ' ' . $ln,
                    'job_position' => $positions[$i % count($positions)],
                    'department' => $depts[$i % count($depts)],
                    'skills' => $skillSets[$i % count($skillSets)],
                    'status' => ($i % 12 === 0) ? 'Nonaktif' : 'Aktif',
                    'birthdate' => "{$birthYear}-{$birthMonth}-{$birthDay}",
                    'phone' => $phoneNum,
                    'address' => $addr,
                ]);
            }

            // 5. Expand CRM Leads to 100 (LEAD-0001 exists -> add LEAD-0002..LEAD-0100)
            $stages = ['New', 'Qualified', 'Proposition', 'Won'];
            $salespeople = ['Kevin Mahendra', 'Aurelia Putri', 'Dinda Kusuma', 'Raisa Permata'];
            for ($i = 2; $i <= 100; $i++) {
                $leadId = 'LEAD-' . str_pad($i, 4, '0', STR_PAD_LEFT);
                $cid = 'C' . str_pad((($i % 95) + 5), 3, '0', STR_PAD_LEFT);
                $stage = $stages[$i % count($stages)];
                $status = ($stage === 'New') ? 'Lead' : 'Opportunity';

                CrmLead::create([
                    'id' => $leadId,
                    'customer_id' => $cid,
                    'name' => 'Paket Skincare & Glowing Clinic #' . $i . ' — ' . $cid,
                    'salesperson' => $salespeople[$i % count($salespeople)],
                    'stage' => $stage,
                    'status' => $status,
                    'quotation_id' => ($stage === 'Won') ? ('S' . str_pad($i, 5, '0', STR_PAD_LEFT)) : null,
                ]);
            }

            // 6. Create 100 Purchase Orders (PO00006 exists -> create PO00001..PO00005 & PO00007..PO00100)
            // Plus 100 Purchase Receipts (WH/IN/00001..WH/IN/00100) and 100 Vendor Bills (BILL/2026/00001..BILL/2026/00100)
            // Interleave dates and transaction sequence properly:
            // Day 1: Opening Balance (2026-01-01)
            // Followed by chronological business cycles (Purchase Receipt -> Vendor Bill -> Vendor Payment -> Delivery COGS -> Customer Invoice -> Customer Payment)
            $poNumbers = [];
            for ($i = 1; $i <= 100; $i++) {
                if ($i === 6) {
                    continue; // Keep baseline PO00006 untouched
                }
                $poNumbers[] = $i;
            }
            $poNumbers[] = 101; // So total POs = 100 (including PO00006)

            foreach ($poNumbers as $idx => $num) {
                $poNum = 'PO' . str_pad($num, 5, '0', STR_PAD_LEFT);
                $vendorId = 'V' . str_pad((($num % 28) + 1), 2, '0', STR_PAD_LEFT);
                $sku = $baseSkus[$num % count($baseSkus)];
                $product = Product::find($sku);
                $unitCost = (float) $product->cost;
                $qtyOrdered = 10 + ($num % 15);
                $untaxed = $unitCost * $qtyOrdered;
                $tax = round($untaxed * 0.11);
                $total = $untaxed + $tax;
                // Strictly chronological dates starting from 2026-01-02 onwards
                $baseDayOffset = $idx * 2 + 1;
                $recDateStr = date('Y-m-d', strtotime('2026-01-01 +' . $baseDayOffset . ' days'));
                $billDateStr = date('Y-m-d', strtotime('2026-01-01 +' . ($baseDayOffset + 1) . ' days'));
                $payDateStr = date('Y-m-d', strtotime('2026-01-01 +' . ($baseDayOffset + 2) . ' days'));

                PurchaseOrder::create([
                    'po_number' => $poNum,
                    'vendor_id' => $vendorId,
                    'payment_terms' => ($num % 2 === 0) ? 'Net 30' : 'Net 14',
                    'status' => 'Done',
                    'untaxed_amount' => $untaxed,
                    'tax_amount' => $tax,
                    'total_amount' => $total,
                    'is_locked' => true,
                    'confirmed_at' => $recDateStr . ' 09:00:00',
                ]);

                $rejectedQty = ($num % 7 === 0) ? 1 : 0;
                $acceptedQty = $qtyOrdered - $rejectedQty;

                PurchaseOrderLine::create([
                    'po_number' => $poNum,
                    'variant_sku' => $sku,
                    'qty_ordered' => $qtyOrdered,
                    'qty_received' => $acceptedQty,
                    'qty_billed' => $acceptedQty,
                    'unit_cost' => $unitCost,
                    'tax_amount' => $tax,
                    'subtotal' => $total,
                ]);

                // Purchase Receipt
                $recNum = 'WH/IN/' . str_pad($num, 5, '0', STR_PAD_LEFT);
                PurchaseReceipt::create([
                    'receipt_number' => $recNum,
                    'po_number' => $poNum,
                    'vendor_id' => $vendorId,
                    'destination_location' => 'WH-JKT',
                    'status' => 'Done',
                    'validated_at' => $recDateStr . ' 11:00:00',
                ]);

                PurchaseReceiptLine::create([
                    'receipt_number' => $recNum,
                    'variant_sku' => $sku,
                    'ordered_qty' => $qtyOrdered,
                    'accepted_qty' => $acceptedQty,
                    'rejected_qty' => $rejectedQty,
                    'unit_cost' => $unitCost,
                ]);

                // Update Stock & StockMove for Accepted Qty
                $quant = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', $sku)->first();
                if ($quant) {
                    $quant->increment('on_hand', $acceptedQty);
                }
                $valuedCost = $unitCost * $acceptedQty;

                StockMove::create([
                    'move_number' => 'SM/IN/' . str_pad($num, 5, '0', STR_PAD_LEFT) . '/' . $sku,
                    'reference_doc_type' => 'Purchase Receipt',
                    'reference_doc_id' => $recNum,
                    'variant_sku' => $sku,
                    'from_location' => null,
                    'to_location' => 'WH-JKT',
                    'qty' => $acceptedQty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $valuedCost,
                    'status' => 'done',
                    'is_quarantine' => false,
                ]);

                if ($rejectedQty > 0) {
                    StockMove::create([
                        'move_number' => 'SM/QUAR/' . str_pad($num, 5, '0', STR_PAD_LEFT) . '/' . $sku,
                        'reference_doc_type' => 'Purchase Receipt',
                        'reference_doc_id' => $recNum,
                        'variant_sku' => $sku,
                        'from_location' => null,
                        'to_location' => 'QUARANTINE',
                        'qty' => $rejectedQty,
                        'unit_cost' => 0,
                        'total_cost' => 0,
                        'status' => 'done',
                        'is_quarantine' => true,
                    ]);
                }

                // Journal for Receipt (Step 1 in P2P Accounting: Dr Inventory 1200 / Cr Stock Interim 1250)
                AccountingService::createJournalEntry(
                    'JE/REC/' . str_pad($num, 5, '0', STR_PAD_LEFT),
                    $recNum,
                    'Purchase Receipt',
                    $recNum,
                    [
                        ['account_code' => '1200', 'debit' => $valuedCost, 'credit' => 0, 'description' => 'Inventory receipt ' . $recNum],
                        ['account_code' => '1250', 'debit' => 0, 'credit' => $valuedCost, 'description' => 'Stock Interim Received ' . $recNum],
                    ],
                    $recDateStr,
                    'Wahyu Saputra'
                );

                // Vendor Bill (Step 2 in P2P Accounting: Three-Way Match on acceptedQty -> Dr Stock Interim 1250, Dr Input VAT 1150 / Cr AP 2100)
                $billNum = 'BILL/2026/' . str_pad($num, 5, '0', STR_PAD_LEFT);
                $billUntaxed = $unitCost * $acceptedQty;
                $billTax = round($billUntaxed * 0.11);
                $billTotal = $billUntaxed + $billTax;
                $isPaidBill = ($num <= 55);

                VendorBill::create([
                    'bill_number' => $billNum,
                    'vendor_id' => $vendorId,
                    'po_number' => $poNum,
                    'receipt_number' => $recNum,
                    'status' => $isPaidBill ? 'Paid' : 'Posted',
                    'untaxed_amount' => $billUntaxed,
                    'tax_amount' => $billTax,
                    'total_amount' => $billTotal,
                    'paid_amount' => $isPaidBill ? $billTotal : 0,
                    'open_amount' => $isPaidBill ? 0 : $billTotal,
                    'posted_at' => $billDateStr . ' 14:00:00',
                ]);

                VendorBillLine::create([
                    'bill_number' => $billNum,
                    'variant_sku' => $sku,
                    'po_qty' => $qtyOrdered,
                    'accepted_qty' => $acceptedQty,
                    'already_billed_qty' => 0,
                    'bill_qty' => $acceptedQty,
                    'unit_cost' => $unitCost,
                    'tax_amount' => $billTax,
                    'subtotal' => $billTotal,
                ]);

                AccountingService::createJournalEntry(
                    'JE/BILL/' . str_pad($num, 5, '0', STR_PAD_LEFT),
                    $poNum,
                    'Vendor Bill',
                    $billNum,
                    [
                        ['account_code' => '1250', 'debit' => $billUntaxed, 'credit' => 0, 'description' => 'Stock Interim Clearing ' . $billNum],
                        ['account_code' => '1150', 'debit' => $billTax, 'credit' => 0, 'description' => 'Input VAT 11% ' . $billNum],
                        ['account_code' => '2100', 'debit' => 0, 'credit' => $billTotal, 'description' => 'Accounts Payable ' . $billNum],
                    ],
                    $billDateStr,
                    'Nanda Kusuma'
                );

                // Step 3 in P2P Accounting: Vendor Payment (Dr AP 2100 / Cr Bank 1010)
                if ($isPaidBill) {
                    $vPayNum = 'PAY/V/2026/' . str_pad($num, 5, '0', STR_PAD_LEFT);
                    $vJeNum = 'JE/PAYV/' . str_pad($num, 5, '0', STR_PAD_LEFT);
                    AccountingService::createJournalEntry(
                        $vJeNum,
                        $billNum,
                        'Vendor Payment',
                        $vPayNum,
                        [
                            ['account_code' => '2100', 'debit' => $billTotal, 'credit' => 0, 'description' => 'AP settlement for ' . $billNum],
                            ['account_code' => '1010', 'debit' => 0, 'credit' => $billTotal, 'description' => 'Vendor payment to ' . $vendorId],
                        ],
                        $payDateStr,
                        'Nanda Kusuma'
                    );

                    Payment::create([
                        'payment_number' => $vPayNum,
                        'payment_type' => 'vendor_payment',
                        'partner_id' => $vendorId,
                        'partner_type' => 'vendor',
                        'bill_id' => $billNum,
                        'amount' => $billTotal,
                        'payment_date' => $payDateStr,
                        'journal_account_code' => '1010',
                        'status' => 'Posted',
                        'journal_entry_number' => $vJeNum,
                    ]);
                }
            }

            // 7. Create 100 Sales Orders (S00006 exists -> create S00001..S00005 & S00007..S00101)
            // Plus 100 Delivery Orders (WH/OUT/00001..WH/OUT/00101) and 100 Customer Invoices (INV/2026/00001..INV/2026/00101)
            $soNumbers = [];
            for ($i = 1; $i <= 100; $i++) {
                if ($i === 6) {
                    continue; // Keep S00006 untouched
                }
                $soNumbers[] = $i;
            }
            $soNumbers[] = 101;

            foreach ($soNumbers as $idx => $num) {
                $soNum = 'S' . str_pad($num, 5, '0', STR_PAD_LEFT);
                // Use customers C007..C100 so C001..C006 baseline credit exposure remains pristine
                $cid = 'C' . str_pad((($num % 90) + 7), 3, '0', STR_PAD_LEFT);
                $sku = $baseSkus[$num % count($baseSkus)];
                $product = Product::find($sku);
                $unitPrice = (float) $product->sales_price;
                $unitCost = (float) $product->cost;
                $qty = 2 + ($num % 5);
                $discount = ($num % 5 === 0) ? 5 : (($num % 9 === 0) ? 10 : 0);
                $untaxed = round(($unitPrice * $qty) * (1 - ($discount / 100)));
                $tax = AccountingService::calculateLineTax($unitPrice, $qty, $discount);
                $total = $untaxed + $tax;
                // Strictly chronological dates for O2C cycle: Delivery -> Invoice -> Payment
                $baseDayOffset = $idx * 2 + 2;
                $doDateStr = date('Y-m-d', strtotime('2026-01-01 +' . $baseDayOffset . ' days'));
                $invDateStr = date('Y-m-d', strtotime('2026-01-01 +' . ($baseDayOffset + 1) . ' days'));
                $cPayDateStr = date('Y-m-d', strtotime('2026-01-01 +' . ($baseDayOffset + 2) . ' days'));

                SalesOrder::create([
                    'order_number' => $soNum,
                    'customer_id' => $cid,
                    'warehouse_code' => 'WH-JKT',
                    'payment_terms' => 'Net 14',
                    'status' => 'Done',
                    'untaxed_amount' => $untaxed,
                    'tax_amount' => $tax,
                    'total_amount' => $total,
                    'created_by' => 'Kevin Mahendra',
                    'confirmed_at' => $doDateStr . ' 10:00:00',
                ]);

                SalesOrderLine::create([
                    'order_number' => $soNum,
                    'variant_sku' => $sku,
                    'qty' => $qty,
                    'delivered_qty' => $qty,
                    'invoiced_qty' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discount,
                    'tax_rate' => 11,
                    'tax_amount' => $tax,
                    'subtotal' => $total,
                ]);

                // Delivery Order
                $doNum = 'WH/OUT/' . str_pad($num, 5, '0', STR_PAD_LEFT);
                DeliveryOrder::create([
                    'do_number' => $doNum,
                    'sales_order_number' => $soNum,
                    'customer_id' => $cid,
                    'source_location' => 'WH-JKT',
                    'status' => 'Done',
                    'is_backorder' => false,
                    'validated_at' => $doDateStr . ' 13:00:00',
                ]);

                DeliveryOrderLine::create([
                    'do_number' => $doNum,
                    'variant_sku' => $sku,
                    'demand_qty' => $qty,
                    'reserved_qty' => $qty,
                    'done_qty' => $qty,
                    'backorder_qty' => 0,
                ]);

                // Deduct stock from WH-JKT
                $quant = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', $sku)->first();
                if ($quant) {
                    $quant->decrement('on_hand', $qty);
                }

                $lineCogs = $unitCost * $qty;
                StockMove::create([
                    'move_number' => 'SM/OUT/' . str_pad($num, 5, '0', STR_PAD_LEFT) . '/' . $sku,
                    'reference_doc_type' => 'Delivery Order',
                    'reference_doc_id' => $doNum,
                    'variant_sku' => $sku,
                    'from_location' => 'WH-JKT',
                    'to_location' => null,
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineCogs,
                    'status' => 'done',
                ]);

                // Step 1 in O2C Accounting: Delivery COGS (Dr COGS 5000 / Cr Inventory 1200)
                AccountingService::createJournalEntry(
                    'JE/COGS/' . str_pad($num, 5, '0', STR_PAD_LEFT),
                    $doNum,
                    'Delivery Order',
                    $doNum,
                    [
                        ['account_code' => '5000', 'debit' => $lineCogs, 'credit' => 0, 'description' => 'COGS for delivery ' . $doNum],
                        ['account_code' => '1200', 'debit' => 0, 'credit' => $lineCogs, 'description' => 'Stock decrease for ' . $doNum],
                    ],
                    $doDateStr,
                    'Wahyu Saputra'
                );

                // Customer Invoice (Step 2 in O2C Accounting: Dr AR 1100 / Cr Revenue 4000, Cr Output VAT 2150)
                $invNum = 'INV/2026/' . str_pad($num, 5, '0', STR_PAD_LEFT);
                $isPaidInv = ($num <= 75);

                CustomerInvoice::create([
                    'invoice_number' => $invNum,
                    'customer_id' => $cid,
                    'source_doc_type' => 'Sales Order',
                    'source_doc_id' => $soNum,
                    'sales_order_number' => $soNum,
                    'delivery_order_number' => $doNum,
                    'status' => $isPaidInv ? 'Paid' : 'Posted',
                    'untaxed_amount' => $untaxed,
                    'tax_amount' => $tax,
                    'total_amount' => $total,
                    'paid_amount' => $isPaidInv ? $total : 0,
                    'open_amount' => $isPaidInv ? 0 : $total,
                    'posted_at' => $invDateStr . ' 15:00:00',
                ]);

                CustomerInvoiceLine::create([
                    'invoice_number' => $invNum,
                    'item_type' => 'product',
                    'item_code' => $sku,
                    'description' => $product->name,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discount,
                    'tax_amount' => $tax,
                    'subtotal' => $total,
                ]);

                AccountingService::createJournalEntry(
                    'JE/INV/' . str_pad($num, 5, '0', STR_PAD_LEFT),
                    $doNum,
                    'Customer Invoice',
                    $invNum,
                    [
                        ['account_code' => '1100', 'debit' => $total, 'credit' => 0, 'description' => 'Customer Invoice ' . $invNum],
                        ['account_code' => '4000', 'debit' => 0, 'credit' => $untaxed, 'description' => 'Sales Revenue ' . $invNum],
                        ['account_code' => '2150', 'debit' => 0, 'credit' => $tax, 'description' => 'Output VAT 11% ' . $invNum],
                    ],
                    $invDateStr,
                    'Nanda Kusuma'
                );

                // Step 3 in O2C Accounting: Customer Payment (Dr Bank 1010 / Cr AR 1100)
                if ($isPaidInv) {
                    $cPayNum = 'PAY/2026/' . str_pad($num, 5, '0', STR_PAD_LEFT);
                    $cJeNum = 'JE/PAY/' . str_pad($num, 5, '0', STR_PAD_LEFT);

                    AccountingService::createJournalEntry(
                        $cJeNum,
                        $invNum,
                        'Customer Payment',
                        $cPayNum,
                        [
                            ['account_code' => '1010', 'debit' => $total, 'credit' => 0, 'description' => 'Customer receipt from ' . $cid . ' for ' . $invNum],
                            ['account_code' => '1100', 'debit' => 0, 'credit' => $total, 'description' => 'Settle AR for ' . $invNum],
                        ],
                        $cPayDateStr,
                        'Nanda Kusuma'
                    );

                    Payment::create([
                        'payment_number' => $cPayNum,
                        'payment_type' => 'customer_payment',
                        'partner_id' => $cid,
                        'partner_type' => 'customer',
                        'invoice_id' => $invNum,
                        'amount' => $total,
                        'payment_date' => $cPayDateStr,
                        'journal_account_code' => '1010',
                        'status' => 'Posted',
                        'journal_entry_number' => $cJeNum,
                    ]);
                }
            }

            // 8. Create 40 Internal Stock Transfers (WH/INT/00001..WH/INT/00040)
            for ($i = 1; $i <= 40; $i++) {
                $trfNum = 'WH/INT/' . str_pad($i, 5, '0', STR_PAD_LEFT);
                $sku = ($i <= 20) ? 'BC-MSK-001' : $baseSkus[$i % count($baseSkus)];
                $qty = ($sku === 'BC-MSK-001') ? 4 : 2;
                $product = Product::find($sku);
                $cost = (float) $product->cost;

                $fromQuant = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', $sku)->first();
                $toQuant = StockQuant::where('location_code', 'ST-BDG')->where('variant_sku', $sku)->first();
                if ($fromQuant && $toQuant && $fromQuant->on_hand >= ($qty + 10)) {
                    $fromQuant->decrement('on_hand', $qty);
                    $toQuant->increment('on_hand', $qty);

                    InternalTransfer::create([
                        'transfer_number' => $trfNum,
                        'from_location' => 'WH-JKT',
                        'to_location' => 'ST-BDG',
                        'variant_sku' => $sku,
                        'qty' => $qty,
                        'status' => 'Done',
                        'transferred_at' => now()->subDays(60 - $i),
                    ]);

                    StockMove::create([
                        'move_number' => 'SM/INT/' . str_pad($i, 5, '0', STR_PAD_LEFT),
                        'reference_doc_type' => 'Internal Transfer',
                        'reference_doc_id' => $trfNum,
                        'variant_sku' => $sku,
                        'from_location' => 'WH-JKT',
                        'to_location' => 'ST-BDG',
                        'qty' => $qty,
                        'unit_cost' => $cost,
                        'total_cost' => $cost * $qty,
                        'status' => 'done',
                        'is_quarantine' => false,
                    ]);
                }
            }

            // 9. Create 50 Beauty Treatments (TRT/2026/00001..TRT/2026/00050)
            for ($i = 1; $i <= 50; $i++) {
                $trtNum = 'TRT/2026/' . str_pad($i, 5, '0', STR_PAD_LEFT);
                $cid = 'C' . str_pad((($i % 90) + 7), 3, '0', STR_PAD_LEFT);
                $eid = ($i % 2 === 0) ? 'E001' : 'E003';
                $dateStr = date('Y-m-d', strtotime('2026-01-01 +' . ($i * 3) . ' days'));
                $payTrtDateStr = date('Y-m-d', strtotime('2026-01-01 +' . ($i * 3 + 1) . ' days'));

                // Ensure ST-BDG has masks
                $maskQuant = StockQuant::where('location_code', 'ST-BDG')->where('variant_sku', 'BC-MSK-001')->first();
                if ($maskQuant && $maskQuant->on_hand > 15) {
                    $maskQuant->decrement('on_hand', 1);
                }

                $invNum = 'INV/TRT/' . str_pad($i, 5, '0', STR_PAD_LEFT);
                BeautyTreatment::create([
                    'treatment_number' => $trtNum,
                    'service_id' => 'SV-FAC-01',
                    'customer_id' => $cid,
                    'employee_id' => $eid,
                    'location_code' => 'ST-BDG',
                    'consumed_sku' => 'BC-MSK-001',
                    'consumed_qty' => 1,
                    'billed_total' => 333000,
                    'status' => 'Done',
                    'invoice_id' => $invNum,
                    'completed_at' => $dateStr . ' 16:00:00',
                ]);

                CustomerInvoice::create([
                    'invoice_number' => $invNum,
                    'customer_id' => $cid,
                    'source_doc_type' => 'Treatment',
                    'source_doc_id' => $trtNum,
                    'status' => 'Paid',
                    'untaxed_amount' => 300000,
                    'tax_amount' => 33000,
                    'total_amount' => 333000,
                    'paid_amount' => 333000,
                    'open_amount' => 0,
                    'posted_at' => $dateStr . ' 16:05:00',
                ]);

                CustomerInvoiceLine::create([
                    'invoice_number' => $invNum,
                    'item_type' => 'service',
                    'item_code' => 'SV-FAC-01',
                    'description' => 'Glow Facial Service',
                    'qty' => 1,
                    'unit_price' => 300000,
                    'discount_percent' => 0,
                    'tax_amount' => 33000,
                    'subtotal' => 333000,
                ]);

                AccountingService::createJournalEntry(
                    'JE/TRT-CONS/' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    $trtNum,
                    'Treatment Consumables',
                    $trtNum,
                    [
                        ['account_code' => '5010', 'debit' => 40000, 'credit' => 0, 'description' => 'Mask consumed for ' . $trtNum],
                        ['account_code' => '1200', 'debit' => 0, 'credit' => 40000, 'description' => 'Stock decrease at ST-BDG'],
                    ],
                    $dateStr,
                    'Laras Kirana'
                );

                AccountingService::createJournalEntry(
                    'JE/TRT-INV/' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    $trtNum,
                    'Customer Invoice',
                    $invNum,
                    [
                        ['account_code' => '1100', 'debit' => 333000, 'credit' => 0, 'description' => 'AR for treatment ' . $trtNum],
                        ['account_code' => '4010', 'debit' => 0, 'credit' => 300000, 'description' => 'Service Revenue for Glow Facial'],
                        ['account_code' => '2150', 'debit' => 0, 'credit' => 33000, 'description' => 'Output VAT 11%'],
                    ],
                    $dateStr,
                    'Laras Kirana'
                );

                $tPayNum = 'PAY/TRT/2026/' . str_pad($i, 5, '0', STR_PAD_LEFT);
                $tJeNum = 'JE/PAYT/' . str_pad($i, 5, '0', STR_PAD_LEFT);
                AccountingService::createJournalEntry(
                    $tJeNum,
                    $invNum,
                    'Customer Payment',
                    $tPayNum,
                    [
                        ['account_code' => '1010', 'debit' => 333000, 'credit' => 0, 'description' => 'Studio payment for ' . $invNum],
                        ['account_code' => '1100', 'debit' => 0, 'credit' => 333000, 'description' => 'Settle AR for ' . $invNum],
                    ],
                    $payTrtDateStr,
                    'Nanda Kusuma'
                );

                Payment::create([
                    'payment_number' => $tPayNum,
                    'payment_type' => 'customer_payment',
                    'partner_id' => $cid,
                    'partner_type' => 'customer',
                    'invoice_id' => $invNum,
                    'amount' => 333000,
                    'payment_date' => $payTrtDateStr,
                    'journal_account_code' => '1010',
                    'status' => 'Posted',
                    'journal_entry_number' => $tJeNum,
                ]);
            }

            // 10. Create 25 Product Returns & Credit Notes (WH/RET/00001..WH/RET/00025)
            // Always dated after the originating invoice's posting date
            for ($i = 1; $i <= 25; $i++) {
                $num = 76 + $i; // Invoices 77..101 are Posted & Unpaid
                $invNum = 'INV/2026/' . str_pad($num, 5, '0', STR_PAD_LEFT);
                $invoice = CustomerInvoice::find($invNum);
                if (!$invoice) {
                    continue;
                }
                $invLine = $invoice->lines()->first();
                if (!$invLine) {
                    continue;
                }

                $retNum = 'WH/RET/' . str_pad($i, 5, '0', STR_PAD_LEFT);
                $sku = $invLine->item_code;
                $product = Product::find($sku);
                $unitPrice = (float) $invLine->unit_price;
                $unitCost = (float) $product->cost;
                $retQty = 1;
                $untaxed = $unitPrice * $retQty;
                $tax = AccountingService::calculateLineTax($unitPrice, $retQty);
                $cnTotal = $untaxed + $tax;

                $invDate = optional($invoice->posted_at)->format('Y-m-d') ?? '2026-06-01';
                $retDateStr = date('Y-m-d', strtotime($invDate . ' +2 days'));
                ProductReturn::create([
                    'return_number' => $retNum,
                    'origin_invoice_number' => $invNum,
                    'customer_id' => $invoice->customer_id,
                    'inspection_status' => 'Sealed / Accepted',
                    'status' => 'Done',
                    'credit_note_total' => $cnTotal,
                    'completed_at' => $retDateStr . ' 16:30:00',
                ]);

                ReturnLine::create([
                    'return_number' => $retNum,
                    'variant_sku' => $sku,
                    'delivered_qty' => $invLine->qty,
                    'already_returned_qty' => 0,
                    'return_qty' => $retQty,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'tax_amount' => $tax,
                    'subtotal' => $cnTotal,
                ]);

                $quant = StockQuant::where('location_code', 'WH-JKT')->where('variant_sku', $sku)->first();
                if ($quant) {
                    $quant->increment('on_hand', $retQty);
                }

                AccountingService::createJournalEntry(
                    'JE/RET-COGS/' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    $retNum,
                    'Return COGS Reversal',
                    $retNum,
                    [
                        ['account_code' => '1200', 'debit' => $unitCost, 'credit' => 0, 'description' => 'Reverse stock valuation for ' . $retNum],
                        ['account_code' => '5000', 'debit' => 0, 'credit' => $unitCost, 'description' => 'Reverse COGS for ' . $retNum],
                    ],
                    $retDateStr,
                    'Wahyu Saputra'
                );

                AccountingService::createJournalEntry(
                    'JE/CN/' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    $invNum,
                    'Credit Note',
                    $retNum,
                    [
                        ['account_code' => '4000', 'debit' => $untaxed, 'credit' => 0, 'description' => 'Credit Note Sales Reversal ' . $retNum],
                        ['account_code' => '2150', 'debit' => $tax, 'credit' => 0, 'description' => 'Credit Note Output VAT Reversal ' . $retNum],
                        ['account_code' => '1100', 'debit' => 0, 'credit' => $cnTotal, 'description' => 'Credit Note AR Adjustment ' . $retNum],
                    ],
                    $retDateStr,
                    'Nanda Kusuma'
                );

                $invoice->update([
                    'open_amount' => max(0, (float) $invoice->open_amount - $cnTotal),
                ]);
            }
        });
    }
}
