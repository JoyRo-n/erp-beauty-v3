# QA DEFECT LOG & ISSUE RESOLUTION REGISTER
**Proyek:** ERP Web App — PT Arunika Beauty Nusantara (NusaGlow)  
**Pemilik Pengujian:** Anggota 4 (QA & Deployment Lead)  
**Terkait:** `TEST_REPORT.md`, `DECISION_LOG.md`, `ROLE_ASSIGNMENT_AND_TEAM_AGREEMENT.md`  
**Status Keseluruhan:** **100% RESOLVED (0 Open Defects)**  

---

## 1. Ringkasan Status Defect

Selama siklus pengujian modul (Sprint Checkpoint Minggu 1 & 2), tim QA melakukan pengujian verifikasi berbasis *boundary value analysis*, uji coba klik ganda (*idempotency*), dan uji batasan wewenang peran (*RBAC*).

| Tingkat Keparahan (*Severity*) | Terdeteksi | Terselesaikan (*Resolved*) | Status Terbuka (*Open*) |
| :--- | :---: | :---: | :---: |
| **Critical / Blocker (P1)** | 3 | 3 | 0 |
| **High / Business Logic (P2)** | 4 | 4 | 0 |
| **Medium / Edge Cases (P3)** | 3 | 3 | 0 |
| **Low / UI Cosmetic (P4)** | 2 | 2 | 0 |
| **TOTAL** | **12** | **12** | **0 (100% Clear)** |

---

## 2. Register Detail Temuan & Resolusi Defect (DEF-01 s/d DEF-12)

| Defect ID | Modul | Modul Owner | Deskripsi Masalah (*Defect Description*) | Dampak Bisnis / Teknis | Langkah Resolusi (*Root Cause & Fix*) | Verifikasi Test |
| :---: | :--- | :---: | :--- | :--- | :--- | :---: |
| **`DEF-01`** | Sales | M1 | Sales Order mengizinkan diskon baris hingga 25% tanpa peringatan. | Erosi margin kotor produk kosmetik di bawah batas toleransi keuangan. | Menambahkan validasi keras di `createSalesOrder`: jika diskon > 15%, tolak simpan dan catat pelanggaran ke `audit_logs` (SR-07 / DL-04). | `T-10` PASS |
| **`DEF-02`** | Sales | M1 | Pelanggan C002 (Nadira Putri) tetap bisa membuat Sales Order meskipun batas kredit terlampaui. | Risiko piutang tak tertagih (*bad debt*) pada pelanggan dengan histori penunggakan. | Mengintegrasikan formula `CreditLimitService` sebelum SO dibuat: $\text{Exposure} + \text{Order Baru} \le \text{Credit Limit}$ (SR-08 / DL-05). | `T-04` PASS |
| **`DEF-03`** | Inventory | M2 | Reservasi stok mengizinkan kuantitas melebihi stok yang tersedia di gudang (*overselling*). | Stok fisik di `WH-JKT` berpotensi menjadi minus (-1). | Memisahkan status stok menjadi On Hand, Reserved, dan Available. Validasi menolak reservasi jika $Qty > Available$ (DL-02). | `T-12` PASS |
| **`DEF-04`** | Inventory | M2 | Menekan tombol "Validate" dua kali pada Surat Jalan `WH/OUT/00007` memotong stok fisik dua kali. | Saldo persediaan berkurang ganda dan jurnal COGS terposting dua kali. | Menambahkan pengecekan status dokumen: jika status sudah `Done`, abaikan proses pengurangan stok dan jurnal (*idempotent*). | `T-14` PASS |
| **`DEF-05`** | Purchasing | M2 | Data pemasok pada Purchase Order dapat diedit setelah status PO dikonfirmasi. | Inkonsistensi data pengadaan dan histori pesanan pemasok dapat dimanipulasi. | Mengunci PO secara permanen (`is_locked = true`) segera setelah dikonfirmasi (SR-21 / DL-18). | `T-20` PASS |
| **`DEF-06`** | Purchasing | M2 | Jumlah unit diterima + ditolak pada Purchase Receipt tidak divalidasi sama dengan kuantitas yang dipesan. | Barang hilang di perjalanan tidak terdeteksi oleh sistem penerimaan gudang. | Menambahkan validasi bahwa $Accepted + Rejected = Ordered Qty$. Jika berbeda, sistem menolak validasi (SR-06). | `T-22` PASS |
| **`DEF-07`** | Invoicing | M4 | Faktur Vendor Bill dapat diterbitkan sebesar 5 unit meski 1 unit ditolak ke karantina. | Keuangan membayar utang atas barang reject pemasok. | Mengunci kuantitas penagihan maksimal sebesar `accepted_qty` (4 unit) pada proses Three-Way Match (DL-03). | `T-24` PASS |
| **`DEF-08`** | Accounting | M4 | Selisih pembulatan PPN 11% antara total pesanan dan total per baris produk. | Laporan rekonsiliasi faktur pajak berbeda dengan nilai subtotal baris. | Menetapkan penghitungan pajak menggunakan pembulatan ke rupiah terdekat per baris produk (`calculateLineTax`) (SR-16). | `T-09` PASS |
| **`DEF-09`** | Accounting | M4 | Pembayaran kas pelanggan (`registerPayment`) mengizinkan nominal lebih besar dari sisa saldo piutang. | Saldo terbuka piutang menjadi negatif dan kas bank tidak akurat. | Menambahkan validasi batas pembayaran: jika nominal > sisa saldo terbuka faktur, batalkan transaksi dengan pesan overpayment (SR-15). | `T-18` PASS |
| **`DEF-10`** | Security | M4 | Pengguna dengan peran Gudang (Warehouse) dapat membuka menu Buku Besar (General Ledger). | Pelanggaran pemisahan tugas (*Segregation of Duties* / SR-19). | Menerapkan otorisasi peran ketat di controller: peran non-keuangan diblokir dengan **HTTP 403 Forbidden** dan insiden dicatat di `audit_logs`. | `T-02 & T-36` PASS |
| **`DEF-11`** | Studio | M3 | Terapis dengan keahlian *Hair Treatment* dapat ditugaskan untuk layanan *Glow Facial*. | Pelanggaran standar kompetensi pelayanan salon kecantikan NusaGlow. | Menambahkan validasi skill karyawan terhadap `required_skill` layanan sebelum perawatan dimulai (SR-17). | `T-32` PASS |
| **`DEF-12`** | System | M2 | Generator nomor pesanan menggunakan `count() + 1` yang berisiko tabrakan (*unique key conflict*) jika ada data yang dihapus. | Error 500 SQLite `UNIQUE constraint failed: sales_orders.order_number`. | Mengganti generator dengan helper urutan numerik tertinggi berbasis prefix (`generateSequence`). | Tested PASS |

---

## 3. Protokol QA Sign-Off

Rangkaian pengujian telah dieksekusi secara otomatis dan manual. Seluruh 12 defect di atas telah diperbaiki, diverifikasi, dan ditutup (*Closed*) pada tanggal 31 Oktober 2026. Sistem dinyatakan memenuhi seluruh Acceptance Criteria dari SR-01 hingga SR-23.

