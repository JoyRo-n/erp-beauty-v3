# TEST REPORT & MATRIKS BUKTI UJI SISTEM ERP
**Proyek:** ERP Web App — PT Arunika Beauty Nusantara (NusaGlow)  
**Dokumen:** Acceptance Test Execution & Verification Report  
**Tanggal Uji:** 2 November 2026  
**Total Pengujian:** 70 Tests | **Total Assertions:** 349 Assertions | **Hasil:** 100% PASS  

---

## 1. Ringkasan Eksekutif

Pengujian sistem dilakukan menggunakan framework pengujian otomatis PHPUnit terhadap seluruh fungsi inti bisnis (*Core Business Services*), skenario integrasi antar-modul, validasi aturan bisnis (*Business Rules Enforcement*), otentikasi login berbasis peran (Email & Password Key Access), dan verifikasi antarmuka web HTTP Odoo 17 (Layar S-01 sampai S-19 beserta aksi POST & Demo Runner).

```
   PASS  Tests\Unit\ExampleTest (1 test, 1 assertion)
   PASS  Tests\Feature\AuthTest (6 tests, 21 assertions — Login, Key Validation, Role Access & Logout)
   PASS  Tests\Feature\BaselineVerificationTest (1 test, 10 assertions)
   PASS  Tests\Feature\ErpBeautyCareTest (38 tests, 266 assertions — T-01 s/d T-38)
   PASS  Tests\Feature\ExampleTest (1 test, 1 assertion)
   PASS  Tests\Feature\HttpScreensTest (23 tests, 50 assertions — S-01 s/d S-19, POST Sales & 1-Click Demo)

  Total Tests:    70 passed
  Total Assertions: 349 passed
  Status:         SUCCESS (0 Failures, 0 Errors, 0 Warnings)
```

---

## 2. Matriks Detail Pengujian T-01 s/d T-38 (Expected vs Actual & Evidence)

| Test ID | Modul | Layar | Situasi / Input Pengujian | Expected Result | Actual Result | Status | Nama Metode Pengujian |
| :---: | :---: | :---: | :--- | :--- | :--- | :---: | :--- |
| **T-01** | System | S-01 | Seed awal; Login peran Sales | Beranda menampilkan 7 modul, Saldo Bank Rp50.000.000, 0 jurnal uji | Bank: 50.000.000; Test Journals: 0 | **PASS** | `test_t01_app_switcher_normal_flow` |
| **T-02** | Security | S-01 | Gudang (Warehouse) buka General Ledger | Akses ditolak (HTTP 403 Forbidden) sesuai SR-19; tercatat di audit log | HTTP 403; Insiden tercatat di audit log | **PASS** | `test_t02_role_access_warehouse_denied_general_ledger` |
| **T-03** | Sales | S-02 | Cek batas kredit C001 (Ayu Prameswari) | Limit Rp2.000.000; Open AR Rp0; Net 14; Total AR perseroan Rp4.440.000 | Nilai presisi sesuai baseline master data | **PASS** | `test_t03_customer_and_credit_terms_normal` |
| **T-04** | Sales | S-02 | C002 coba SO 3 unit BC-TON-100 | Total exposure Rp721.500 > Limit Rp500.000 &rarr; Ditolak sistem (SR-08) | Konfirmasi ditolak karena limit terlampaui | **PASS** | `test_t04_credit_limit_exposure_rejection` |
| **T-05** | Inventory | S-03 | Buka varian BC-LIP-001 & BC-LIP-002 | 2 SKU unik berbeda, harga jual Rp65.000, cost Rp30.000 | 2 SKU unik terdaftar (Rose & Coral) | **PASS** | `test_t05_product_variant_sku_normal` |
| **T-06** | Inventory | S-03 | Coba buat SKU duplikat BC-LIP-001 | Sistem menolak duplikasi SKU (SR-13) | QueryException tertangkap, integritas terjaga | **PASS** | `test_t06_duplicate_variant_sku_rejected` |
| **T-07** | CRM | S-04 | Konversi LEAD-0001 jadi Opportunity | 1 Opportunity terbuat; mutasi stok 0; no jurnal; AR tetap Rp4.440.000 | Lead berpindah tahap tanpa mutasi persediaan | **PASS** | `test_t07_crm_lead_to_opportunity` |
| **T-08** | CRM | S-04 | Konfirmasi penawaran tanpa customer | Ditolak karena pelanggan wajib diisi (SR-12) | Validasi gagal, sistem menolak konfirmasi | **PASS** | `test_t08_confirm_quotation_without_customer_rejected` |
| **T-09** | Sales | S-05 | C001 pesan 2 BC-CLN-100 tanpa diskon | Untaxed Rp240.000, PPN Rp26.400, Total Rp266.400, Reservasi stok +2 | Perhitungan PPN 11% & reservasi stok +2 sukses | **PASS** | `test_t09_quotation_to_sales_order` |
| **T-10** | Sales | S-05 | Sales coba beri diskon 20% pada produk | Ditolak karena diskon melebihi batas 15% (SR-07 / DL-04) | Ditolak dengan pesan diskon melebihi 15% | **PASS** | `test_t10_discount_over_15_percent_rejected` |
| **T-11** | Inventory | S-06 | SO 4 unit BC-CLN-100 dari WH-JKT | On Hand 15; Reserved naik dari 3 jadi 7; Available turun dari 12 jadi 8 | Reserved = 7, Available = 8 | **PASS** | `test_t11_stock_reservation_normal` |
| **T-12** | Inventory | S-06 | Reservasi 13 unit saat Available hanya 8 | Ditolak karena stok tidak mencukupi (SR-03/SR-04 / DL-02) | Ditolak: Insufficient stock available to reserve | **PASS** | `test_t12_over_reservation_rejected` |
| **T-13** | Inventory | S-07 | Pengiriman parsial 2 dari 4 unit | On Hand 13; Terbit Backorder 2 unit; Jurnal COGS Rp120.000 terposting | Backorder terbit otomatis; Jurnal seimbang | **PASS** | `test_t13_partial_delivery_and_backorder` |
| **T-14** | Inventory | S-07 | Klik validasi ulang pada DO yang sama | Idempotent (SR-11): tidak ada mutasi stok atau jurnal ganda | Mutasi diabaikan; tidak ada duplikasi data | **PASS** | `test_t14_delivery_order_validation_idempotency` |
| **T-15** | Invoicing | S-08 | Post Invoice untuk 2 unit terkirim | Untaxed Rp240.000, PPN Rp26.400, Piutang (AR) bertambah Rp266.400 | Invoice posted; Piutang AR bertambah Rp266.400 | **PASS** | `test_t15_delivery_based_customer_invoice_normal` |
| **T-16** | Invoicing | S-08 | Buat invoice 4 unit sebelum barang dikirim | Ditolak: Tagihan hanya untuk kuantitas terkirim (DL-01) | Ditolak: Cannot invoice undelivered quantities | **PASS** | `test_t16_invoice_before_delivery_rejected` |
| **T-17** | Payment | S-09 | Bayar parsial Rp100.000 atas INV Rp266.400 | Piutang terbuka turun jadi Rp166.400; Kas bank bertambah Rp100.000 | Saldo terbuka Rp166.400; Kas bank bertambah | **PASS** | `test_t17_partial_payment_normal` |
| **T-18** | Payment | S-09 | Coba bayar Rp300.000 atas INV Rp266.400 | Ditolak karena pembayaran melebihi saldo piutang (SR-15 / DL-12) | Ditolak: Payment amount exceeds open invoice | **PASS** | `test_t18_overpayment_rejected` |
| **T-19** | Purchase | S-10 | Buat PO 5 BC-TON-100 @ Rp70.000 | Untaxed Rp350.000, Tax Rp38.500; Utang (AP) belum bertambah | PO Confirmed; Hutang usaha belum tercatat | **PASS** | `test_t19_rfq_to_purchase_order_normal` |
| **T-20** | Purchase | S-10 | Ubah data vendor setelah PO dikonfirmasi | Ditolak karena PO terkunci setelah konfirmasi (SR-21 / DL-18) | Ditolak: Purchase Order is confirmed and locked | **PASS** | `test_t20_confirmed_po_locked_vendor_change_rejected` |
| **T-21** | Purchase | S-11 | Terima 5 unit: 4 diterima, 1 ditolak | WH-JKT bertambah 4 unit (+Rp280.000); 1 unit masuk karantina non-valued | 4 unit dinilai; 1 unit karantina nilai Rp0 | **PASS** | `test_t21_purchase_receipt_accepted_and_rejected` |
| **T-22** | Purchase | S-11 | Validasi rejected tanpa persetujuan QC | Ditolak: Jumlah diterima + ditolak harus sama dengan PO | Validasi gagal, integritas kuantitas terjaga | **PASS** | `test_t22_validate_rejected_without_approval_rejected` |
| **T-23** | Invoicing | S-12 | Post Vendor Bill atas 4 unit diterima | Untaxed Rp280.000, PPN Rp30.800, Utang bertambah Rp310.800 | Bill posted; Interim inventory clearing seimbang | **PASS** | `test_t23_vendor_bill_three_way_match_normal` |
| **T-24** | Invoicing | S-12 | Vendor menagih 5 unit meski diterima 4 | Ditolak: Tagihan dibatasi kuantitas diterima (SR-05/06 / DL-03) | Ditolak: Bill qty cannot exceed accepted qty | **PASS** | `test_t24_vendor_bill_exceeding_accepted_rejected` |
| **T-25** | Accounting | S-13 | Verifikasi jurnal pengeluaran 2 unit | Debit Beban Pokok Penjualan = Kredit Persediaan (Rp120.000) | Jurnal seimbang Debit = Credit (Rp120.000) | **PASS** | `test_t25_ledger_posting_and_cogs_balance` |
| **T-26** | Accounting | S-13 | Kirim ulang posting jurnal yang sama | Idempotent (SR-11): baris jurnal tidak digandakan | Mutasi jurnal tidak berulang | **PASS** | `test_t26_journal_post_idempotency` |
| **T-27** | Inventory | S-14 | Transfer 3 unit BC-SER-030 JKT &rarr; BDG | WH-JKT -3; ST-BDG +3; Total valuasi perseroan tetap Rp22.612.500 | Mutasi antarlokasi sukses; Valuasi tidak berubah | **PASS** | `test_t27_internal_stock_transfer_normal` |
| **T-28** | Inventory | S-14 | Transfer 999 unit dari WH-JKT (stok 19) | Ditolak karena stok gudang asal tidak mencukupi (SR-04/14) | Ditolak: Insufficient stock at source WH-JKT | **PASS** | `test_t28_insufficient_stock_transfer_rejected` |
| **T-29** | Sales | S-15 | Retur 1 unit terkirim & terbitkan Credit Note | Credit Note Rp133.200 terbit, stok pulih +1, jurnal COGS dibalik | Stok pulih; COGS neto Rp60rb; Piutang berkurang | **PASS** | `test_t29_return_and_credit_note_normal` |
| **T-30** | Sales | S-15 | Coba retur 3 unit padahal hanya dikirim 2 | Ditolak karena retur melebihi barang terkirim (SR-10 / DL-07) | Ditolak: Return qty exceeds delivered quantity | **PASS** | `test_t30_return_exceeding_delivered_rejected` |
| **T-31** | Studio | S-16 | Tugaskan E001 (keahlian Facial) ke Facial | Penugasan sukses; tanpa side-effect payroll; stok belum terpotong | Penugasan terapis tersertifikasi sukses | **PASS** | `test_t31_employee_assignment_normal` |
| **T-32** | Studio | S-16 | Tugaskan E002 (Hair Treatment) ke Facial | Ditolak karena terapis tidak memiliki skill Facial (SR-17 / DL-14) | Ditolak: Employee lacks required Facial skill | **PASS** | `test_t32_incompetent_therapist_assignment_rejected` |
| **T-33** | Studio | S-17 | Selesaikan Glow Facial C004 oleh E001 | Masker ST-BDG -1; COGS Rp40.000; Invoice jasa Rp333.000 terposting | Bahan berkurang 1; Jurnal COGS & Invoice terbit | **PASS** | `test_t33_beauty_treatment_completion_integrated` |
| **T-34** | Studio | S-17 | Klik selesai berulang kali pada treatment | Idempotent (SR-11/18): masker tidak dipotong dobel | Duplikasi aksi diabaikan sistem | **PASS** | `test_t34_treatment_completion_idempotency` |
| **T-35** | Dashboard | S-18 | Verifikasi KPI skenario pasca transaksi | Pendapatan Rp240rb, COGS Rp120rb, Laba Rp120rb, Piutang Rp266.400 | Angka kalkulasi KPI presisi sesuai skenario | **PASS** | `test_t35_dashboard_scenario_kpis` |
| **T-36** | Dashboard | S-18 | Peran Warehouse buka KPI finansial | Akses diblokir (HTTP 403 Forbidden) & dicatat audit (SR-19/20) | Akses diblokir; Insiden tercatat di audit log | **PASS** | `test_t36_warehouse_role_blocked_from_ar_and_general_ledger` |
| **T-37** | Finance | S-19 | Bayar lunas Vendor Bill Rp310.800 via Bank | Utang terbuka menjadi Rp0; Kas Bank berkurang tepat Rp310.800 | Utang lunas; Saldo bank berkurang akurat | **PASS** | `test_t37_vendor_payment_settlement_normal` |
| **T-38** | Finance | S-19 | Coba bayar Rp400.000 atas Bill Rp310.800 | Ditolak karena melebihi utang terbuka (SR-23 / DL-19) | Ditolak: Payment amount exceeds open AP amount | **PASS** | `test_t38_vendor_overpayment_rejected` |

---

## 3. Hasil Pengujian HTTP Antarmuka Layar (S-01 s/d S-19)

| Layar | Rute URL | Peran Uji | HTTP Status | Elemen yang Terverifikasi |
| :---: | :--- | :---: | :---: | :--- |
| **S-01** | `/` | Sales | 200 OK | PT Arunika Beauty Nusantara, App Switcher, Indikator Saldo Bank |
| **S-02** | `/customers` | Sales | 200 OK | Master Customer, Credit Terms, Batas Limit, Exposure Warning |
| **S-03** | `/products` | Inventory | 200 OK | Katalog Produk, Varian SKU (Rose & Coral), Barcode, Sales Price |
| **S-04** | `/crm` | Sales | 200 OK | Pipeline Lead & Opportunity, Stage Conversion, Tombol Buat Quotation |
| **S-05** | `/sales` | Sales | 200 OK | Sales Order Lines, Diskon Maksimal 15%, Perhitungan PPN 11% |
| **S-06** | `/inventory/stock` | Warehouse | 200 OK | Stock Quants Gudang Jakarta (`WH-JKT`) & Studio Bandung (`ST-BDG`) |
| **S-07** | `/inventory/delivery` | Warehouse | 200 OK | Delivery Order Form, Tombol Validate, Pemicu Dokumen Backorder |
| **S-08** | `/invoicing/customer` | Finance | 200 OK | Customer Invoice Form, Delivered Quantities Filter, Post Invoice |
| **S-09** | `/invoicing/payments` | Finance | 200 OK | Form Pembayaran Pelanggan, Registrasi Kas Masuk, Anti-Overpayment |
| **S-10** | `/purchase` | Purchasing | 200 OK | Purchase Order Form, Konfirmasi PO, Penguncian Data Pemasok |
| **S-11** | `/purchase/receipts` | Warehouse | 200 OK | Penerimaan Barang, Pemisahan Accepted vs Rejected (Area Karantina) |
| **S-12** | `/purchase/bills` | Finance | 200 OK | Vendor Bill Three-Way Matching, Validasi Akun Utang Usaha |
| **S-13** | `/accounting/general-ledger` | Finance | 200 OK | Buku Besar, Rekonsiliasi Saldo Kas, Piutang, Persediaan, dan Modal |
| **S-13** | `/accounting/general-ledger` | Warehouse | 403 Forbidden | Verifikasi Keamanan RBAC (Warehouse dilarang melihat buku besar) |
| **S-14** | `/inventory/transfers` | Warehouse | 200 OK | Internal Transfer Gudang Jakarta ke Studio Bandung, Valuasi Tetap |
| **S-15** | `/sales/returns` | Sales | 200 OK | Retur Barang Tersegel, Pemulihan Fisik Stok, Penerbitan Credit Note |
| **S-16** | `/employees` | Studio | 200 OK | Master Data Pegawai, Matriks Sertifikasi Keahlian (*Skill Matrix*) |
| **S-17** | `/studio/treatments` | Studio | 200 OK | Form Pelaksanaan Treatment, Konsumsi Otomatis 1 Masker, Auto-Invoice |
| **S-18** | `/accounting/dashboard` | Finance | 200 OK | Executive Dashboard, Drill-down KPI Transaksi, Audit Trail Log |
| **S-18** | `/accounting/dashboard` | Warehouse | 403 Forbidden | Proteksi RBAC Dashboard Finansial dari staf Gudang |
| **S-19** | `/accounting/vendor-payments` | Finance | 200 OK | Pelunasan Tagihan Pemasok, Rekonsiliasi Pengeluaran Kas Bank |
