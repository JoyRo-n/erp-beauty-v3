# DECISION LOG — SISTEM ERP PT ARUNIKA BEAUTY NUSANTARA (NUSAGLOW)
**Proyek:** ERP Web App — PT Arunika Beauty Nusantara  
**Pemilik Dokumen:** Tim Pengembang ERP (Anggota 1–4)  
**Versi:** 1.0 (Final Ratified)  
**Standar Antarmuka:** Odoo Web 17.0 Pattern  
**Status:** Disetujui & Terimplementasi Penuh (Verified by Automated Test Suite T-01 s/d T-38)

---

## 1. Tujuan Dokumen

Decision Log ini mencatat keputusan arsitektural dan fungsional yang diambil tim dalam menyelesaikan konflik kebutuhan antar-stakeholder (Direktur, Finance, Sales, Warehouse, Purchasing, dan Studio Operasional). Setiap keputusan mengacu pada:
* **Stakeholder Requirements (SR-01 s/d SR-25)**
* **Functional Requirements (F-01 s/d F-25)**
* **Skenario Layar Odoo (S-01 s/d S-19)**
* **Test Case Otomatis (T-01 s/d T-38)**

---

## 2. Matriks Keputusan Utama (DL-01 s/d DL-19)

### DL-01 — Waktu Pembuatan Customer Invoice
* **Konflik:** SR-01 (Direktur: Ingin invoice 100% saat SO dikonfirmasi agar kas cepat masuk) vs. SR-02 (Finance: Invoice harus sesuai barang yang sudah dikirim dan divalidasi).
* **Keputusan:** **Opsi B (Delivered Quantities Invoicing)**. Customer Invoice hanya dapat diterbitkan atas kuantitas barang yang telah divalidasi pada Delivery Order (neto retur).
* **Alasan:** Menghindari pengakuan pendapatan fiktif (*revenue misstatement*) atas barang yang belum diserahterimakan, serta menjaga keselarasan antara mutasi persediaan, piutang, dan COGS.
* **Trade-off & Mitigasi:** Nilai pesanan belum bisa ditagihkan seluruhnya jika terjadi pengiriman bertahap (*partial delivery*). Sisa barang yang belum dikirim dilacak sebagai Backorder dan belum ditagihkan.
* **Bukti Pengujian:** **T-15** (Invoice normal atas 2 unit terkirim) & **T-16** (Penolakan invoice sebelum delivery).

---

### DL-02 — Konfirmasi Pesanan Ketika Stok Tidak Cukup
* **Konflik:** SR-03 (Sales Manager: Ingin SO selalu dapat dikonfirmasi demi menjaga kepuasan pelanggan) vs. SR-04 (Warehouse Head: Melarang keras stok negatif).
* **Keputusan:** **Opsi B (Confirm Allowed, Hard Reservation Block)**. Sales Order boleh dikonfirmasi, tetapi reservasi dan pengeluaran fisik dilarang membuat On Hand atau Available menjadi negatif.
* **Alasan:** Menampung permintaan pasar tanpa merusak integritas data gudang fisik.
* **Trade-off & Mitigasi:** Sales wajib menginformasikan bahwa sebagian pesanan berstatus *backorder* menunggu pasokan. UI menampilkan pemisahan jelas antara On Hand, Reserved, dan Available.
* **Bukti Pengujian:** **T-11** (Reservasi normal 4 unit) & **T-12** (Penolakan percobaan reservasi 13 unit melebihi stok tersedia).

---

### DL-03 — Vendor Bill untuk Barang yang Belum Diterima / Ditolak
* **Konflik:** SR-05 (Purchasing Lead: Ingin seluruh PO langsung ditagihkan agar proses cepat) vs. SR-06 (Finance: Barang yang ditolak QC dilarang menambah utang & valuasi).
* **Keputusan:** **Opsi B (Three-Way Matching on Accepted Quantities Only)**. Tagihan pemasok (Vendor Bill) dibatasi maksimum sebesar kuantitas yang berstatus *Accepted* pada Purchase Receipt.
* **Alasan:** Menghindari pembayaran atas barang cacat atau barang yang belum tiba fisik di gudang.
* **Trade-off & Mitigasi:** Purchasing harus menunggu proses inspeksi gudang. Barang reject ditempatkan di lokasi karantina non-valued (nilai Rp0) dan diisolasi dari akun utang usaha (AP).
* **Bukti Pengujian:** **T-21** (Penerimaan 4 accepted, 1 rejected karantina), **T-23** (Vendor Bill atas 4 unit), dan **T-24** (Penolakan penagihan 5 unit).

---

### DL-04 — Kebijakan Batas Diskon Penjualan
* **Konflik:** SR-07 (Sales: Ingin wewenang diskon hingga 25% demi menutup transaksi) vs. SR-08 (Finance: Membatasi margin erosi maksimal 15%).
* **Keputusan:** Batas diskon baris pesanan dikunci maksimal **15%**. Setiap input diskon > 15% ditolak secara otomatis oleh sistem. Harga dan diskon terkunci permanen setelah SO berstatus Confirmed.
* **Alasan:** Menjaga margin kontribusi kotor agar tidak terjadi kerugian operasional per transaksi.
* **Bukti Pengujian:** **T-10** (Penolakan diskon 20% pada Sales Order).

---

### DL-05 — Formula Perhitungan Credit Exposure Pelanggan
* **Konflik:** SR-08 (Finance: Risiko gagal bayar piutang pelanggan lama yang sering menunggak) vs. SR-03 (Sales: Kemudahan transaksi kredit).
* **Keputusan:** Credit Exposure dihitung secara komprehensif dengan formula:  
  $$\text{Credit Exposure} = \text{Opening AR} + \text{Unpaid Posted Invoices} + \text{Unbilled Confirmed Orders}$$  
  Jika $(\text{Exposure} + \text{Nilai Order Baru}) > \text{Credit Limit}$, konfirmasi SO otomatis diblokir.
* **Alasan:** Melindungi arus kas perseroan dari *bad debt* (contoh: C002 Nadira Putri dengan limit Rp500.000).
* **Bukti Pengujian:** **T-03** (Cek master limit C001) & **T-04** (Penolakan order C002 karena exposure melebihi batas).

---

### DL-06 — Mekanisme Backorder Pengiriman Parsial
* **Konflik:** Pemenuhan pesanan parsial sering membingungkan antara sisa barang dan barang yang sudah terkirim.
* **Keputusan:** Ketika Delivery Order divalidasi dengan kuantitas parsial (misal: kirim 2 dari 4 unit), sistem secara otomatis mencatat `delivered_qty = 2` pada SO dan menerbitkan dokumen **Backorder** baru dengan nomor terhubung (`WH/OUT/00007-BO`) untuk sisa 2 unit.
* **Bukti Pengujian:** **T-13** (Partial delivery 2 unit dan penerbitan otomatis dokumen Backorder).

---

### DL-07 — Retur Penjualan dan Penerbitan Credit Note
* **Konflik:** Permintaan retur pelanggan apakah langsung mengembalikan uang tunai atau memotong piutang.
* **Keputusan:** Retur hanya diizinkan untuk barang yang telah berstatus Delivered dan belum pernah diretur sebelumnya. Retur barang tersegel memulihkan stok fisik, membalik COGS, dan menerbitkan **Credit Note (Reverse Invoice)** secara otomatis untuk mengurangi saldo piutang.
* **Bukti Pengujian:** **T-29** (Retur 1 unit terkirim & penerbitan Credit Note) & **T-30** (Penolakan retur melebihi unit terkirim).

---

### DL-08 — Idempotensi Eksekusi Transaksi (Safe Duplicate Clicks)
* **Konflik:** SR-11 (Koneksi jaringan tidak stabil atau pengguna menekan tombol konfirmasi/validasi berulang kali).
* **Keputusan:** Seluruh endpoint konfirmasi transaksi (Validate DO, Post Invoice, Post Receipt, Post Bill, Complete Treatment) bersifat **idempotent**. Klik berulang tidak akan menggandakan mutasi stok maupun jurnal akuntansi.
* **Bukti Pengujian:** **T-14** (Idempotensi validasi DO), **T-26** (Idempotensi posting jurnal), dan **T-34** (Idempotensi penyelesaian treatment).

---

### DL-09 — Jejak Audit Menyeluruh (Audit Trail)
* **Konflik:** Ketidakjelasan penanggung jawab ketika terjadi pembatalan atau anomali transaksi.
* **Keputusan:** Setiap aksi penting (Create, Confirm, Validate, Post, Rejection, Access Denied) mencatat jejak audit ke tabel `audit_logs` dan `log_notes` (Chatter) mencakup: `user_name`, `role`, `action`, `document_type`, `document_id`, `timestamp`, dan `details`.
* **Bukti Pengujian:** **T-02** & **T-36** (Pencatatan insiden penolakan akses ke audit log).

---

### DL-10 — Standarisasi SKU Varian Produk
* **Konflik:** SR-13 (Kerancuan pencatatan produk kosmetik yang memiliki varian warna/aroma berbeda namun kode produk induk sama).
* **Keputusan:** Setiap varian produk wajib memiliki **SKU unik** pada level terkecil (contoh: `BC-LIP-001` untuk Rose, `BC-LIP-002` untuk Coral). Sistem menolak pembuatan SKU duplikat.
* **Bukti Pengujian:** **T-05** (Varian unik normal) & **T-06** (Penolakan SKU duplikat).

---

### DL-11 — Mutasi Stok Antar-Gudang (Internal Transfer)
* **Konflik:** Transfer barang dari Gudang Jakarta (`WH-JKT`) ke Studio Bandung (`ST-BDG`) berisiko mengubah nilai valuasi perseroan.
* **Keputusan:** Internal Transfer memindahkan kuantitas fisik antar-lokasi tanpa mengubah total valuasi persediaan perusahaan (Rp22.612.500). Sistem memvalidasi ketersediaan stok di lokasi asal sebelum memproses transfer.
* **Bukti Pengujian:** **T-27** (Transfer normal 3 unit) & **T-28** (Penolakan transfer 999 unit karena stok tidak cukup).

---

### DL-12 — Validasi Pembayaran Pelanggan & Anti-Overpayment
* **Konflik:** Kesalahan input nominal pembayaran yang melebihi tagihan terbuka.
* **Keputusan:** Sistem mendukung pembayaran bertahap (parsial), namun secara ketat menolak pembayaran yang melebihi sisa saldo piutang terbuka (*no overpayment allowed*).
* **Bukti Pengujian:** **T-17** (Pembayaran parsial normal) & **T-18** (Penolakan overpayment).

---

### DL-13 — Perhitungan Pajak Pertambahan Nilai (PPN 11%)
* **Konflik:** Selisih pembulatan sen antara subtotal dan pajak pada faktur multi-baris.
* **Keputusan:** Pajak PPN 11% dihitung per baris transaksi dan dibulatkan ke rupiah terdekat. Setiap jurnal posting akuntansi memvalidasi bahwa total Debit dan total Credit harus seimbang presisi (selisih Rp0).
* **Bukti Pengujian:** **T-09** (Kalkulasi PPN 11% pada SO) & **T-25** (Keseimbangan debit-kredit jurnal COGS).

---

### DL-14 — Matriks Kualifikasi Pegawai Studio Kecantikan
* **Konflik:** SR-17 (Risiko malpraktik atau komplain pelanggan jika terapis yang ditugaskan tidak memiliki sertifikasi keahlian yang sesuai).
* **Keputusan:** Sesi perawatan kecantikan (Beauty Treatment) hanya dapat ditugaskan kepada staf berstatus Aktif yang memiliki skill bersesuaian pada matriks keahliannya (contoh: Glow Facial wajib memiliki keahlian *Facial*).
* **Bukti Pengujian:** **T-31** (Penugasan terapis bersertifikasi normal) & **T-32** (Penolakan penugasan terapis tanpa keahlian yang sesuai).

---

### DL-15 — Konsumsi Otomatis Bahan Studio & Integrasi Tagihan
* **Konflik:** SR-16 & SR-18 (Bahan perawatan studio sering tidak tercatat pemakaiannya sehingga stok fisik selisih dengan sistem).
* **Keputusan:** Saat treatment dinyatakan *Completed*, sistem secara otomatis: (1) Mengurangi stok bahan habis pakai (1 sachet `BC-MSK-001` dari `ST-BDG`), (2) Memposting jurnal beban pemakaian bahan (COGS Rp40.000), dan (3) Menerbitkan Customer Invoice jasa senilai Rp333.000 secara otomatis.
* **Bukti Pengujian:** **T-33** (Penyelesaian treatment terintegrasi mutasi bahan, jurnal, dan invoice).

---

### DL-16 — Kontrol Akses Berbasis Peran (RBAC Enforcement)
* **Konflik:** SR-19 & SR-20 (Departemen operasional non-keuangan seperti Gudang dapat melihat data sensitif laba rugi dan buku besar).
* **Keputusan:** Sistem menerapkan proteksi RBAC ketat di level controller dan middleware. Pengguna berstatus *Warehouse* diblokir total (HTTP 403 Forbidden) saat mencoba mengakses General Ledger atau Dashboard Finansial, dan insiden tersebut otomatis dicatat ke Audit Log.
* **Bukti Pengujian:** **T-02** & **T-36** (Pemblokiran akses Warehouse ke General Ledger & Financial Dashboard).

---

### DL-17 — Drill-down Interaktif Dashboard Eksekutif
* **Konflik:** Direktur dan Manajemen membutuhkan angka ringkasan KPI yang dapat ditelusuri sumbernya hingga ke nomor dokumen asli.
* **Keputusan:** Seluruh widget pada Dashboard Eksekutif (Revenue, COGS, Gross Margin, Open AR, Valuation) dilengkapi tombol tautan langsung (*drill-down*) ke daftar dokumen sumber (Sales Order, Journal Entries, Customer Invoices, Stock Quants).
* **Bukti Pengujian:** **T-35** (Verifikasi kalkulasi KPI skenario pada dashboard).

---

### DL-18 — Penguncian Purchase Order (PO Lock After Confirmation)
* **Konflik:** SR-21 (Risiko manipulasi data vendor atau harga satuan setelah PO disetujui pimpinan).
* **Keputusan:** Purchase Order yang telah berstatus *Confirmed* dikunci secara permanen. Penggantian nama vendor atau manipulasi harga barang ditolak oleh sistem.
* **Bukti Pengujian:** **T-19** (PO normal) & **T-20** (Penolakan perubahan vendor pada PO yang sudah confirmed).

---

### DL-19 — Pelunasan Tagihan Pemasok & Rekonsiliasi Kas Bank
* **Konflik:** SR-23 (Pembayaran kepada pemasok yang tidak tercatat nomor tagihannya atau membayar melebihi sisa utang).
* **Keputusan:** Pembayaran vendor hanya dapat diproses terhadap Vendor Bill berstatus *Posted*, dengan nominal maksimum sebesar saldo utang terbuka tagihan tersebut. Saldo kas bank berkurang tepat sesuai nominal pembayaran.
* **Bukti Pengujian:** **T-37** (Pelunasan utang normal Rp310.800) & **T-38** (Penolakan pembayaran melebihi saldo utang).
