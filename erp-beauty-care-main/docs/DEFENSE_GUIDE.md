# DEFENSE & PRESENTATION GUIDE — SISTEM ERP PT ARUNIKA
**Proyek:** ERP Web App — PT Arunika Beauty Nusantara (NusaGlow)  
**Dokumen:** Panduan Tanya Jawab Presentasi & Ujian Sidang Dosen  
**Fokus Penilaian:** Kesesuaian Kebutuhan Bisnis (SR), Solusi Konflik (DL), Desain Antarmuka Odoo 17, dan Integritas Data Akuntansi & Persediaan.

---

## 1. Pertanyaan Kunci: Mengapa Fitur-Fitur Ini yang Diprioritaskan?

### Jawaban Strategis:
> *"Pak / Bu Penguji, prioritas pengembangan sistem ini dirancang berdasarkan **keterkaitan kritis antara arus fisik barang (Logistik) dan arus pencatatan moneter (Akuntansi)** pada PT Arunika Beauty Nusantara yang memiliki dua lini bisnis sekaligus: **Retail Produk Skincare** dan **Layanan Studio Kecantikan**. 
>
> Tanpa kontrol sistem yang ketat, perseroan rentan mengalami tiga masalah fatal:
> 1. **Piutang Macet (*Bad Debts*)** akibat penjualan tanpa batas kredit terukur.
> 2. **Stok Fisik Selisih (*Phantom Stock*)** karena barang studio dipakai tanpa pencatatan mutasi dan barang reject pemasok salah diakui sebagai aset.
> 3. **Laporan Keuangan Bias / Fiktif** karena pendapatan ditagihkan sebelum barang dikirim."*

---

## 2. Bedah Skenario Konflik Stakeholder (Decision Defense)

### Pertanyaan 1: "Kenapa invoice tidak langsung dibuat saat pesanan Sales Order dikonfirmasi? Bukankah itu bagus untuk mempercepat kas masuk seperti maunya Direktur?"
* **Jawaban:** 
  - Sesuai prinsip akuntansi konservatif dan standar PSAK / IFRS, penagihan sebelum penyerahan barang melanggar prinsip *matching principle* dan pengakuan pendapatan fiktif.
  - Jika barang yang belum dikirim ditagihkan, saat terjadi pengiriman parsial (*partial delivery*) atau barang rusak, perusahaan harus merepotkan finance dengan banyak jurnal koreksi.
  - **Keputusan kita (DL-01 / Opsi B):** Faktur hanya diakui atas *delivered quantity*. Sisa barang dicatat sebagai *Backorder* resmi yang siap dikirim saat stok tersedia. Bukti pengujian terbukti pada **T-15** dan **T-16**.

### Pertanyaan 2: "Bagaimana cara sistem mencegah Sales Order membuat stok gudang menjadi negatif?"
* **Jawaban:**
  - Kami membedakan status kuantitas di setiap gudang menjadi 3 tingkat: **On Hand (Fisik)**, **Reserved (Terkunci Pesanan)**, dan **Available (Siap Dijual)**.
  - Sesuai kesepakatan **DL-02**, Sales Order tetap boleh dikonfirmasi untuk menangkap *demand* pasar, namun reservasi stok fisik dan penerbitan surat jalan (*Delivery Order*) dibatasi hanya sampai kuantitas *Available* habis.
  - Pengujian **T-11** dan **T-12** membuktikan percobaan mereservasi 13 unit saat sisa kuantitas hanya 8 unit langsung ditolak sistem.

### Pertanyaan 3: "Ketika ada barang dari vendor yang ditolak QC, bagaimana sistem mencatatnya di persediaan dan utang?"
* **Jawaban:**
  - Sesuai **DL-03 (Three-Way Matching)**, barang yang ditolak dialihkan ke lokasi karantina khusus dengan valuasi **Rp0**.
  - Bagian Keuangan (Finance) hanya dapat membuat Vendor Bill atas kuantitas yang berstatus *Accepted*. Barang reject tidak menambah akun Utang Dagang (2100) maupun Nilai Persediaan (1200).
  - Terbukti pada pengujian **T-21** (4 diterima, 1 ditolak), **T-23** (bill untuk 4 unit), dan **T-24** (sistem memblokir penagihan 5 unit).

### Pertanyaan 4: "Bagaimana integrasi otomatis antara Studio Kecantikan dengan Gudang dan Keuangan?"
* **Jawaban:**
  - Saat terapis menyelesaikan perawatan (*Glow Facial*), sistem secara atomik mengeksekusi 3 proses sekaligus:
    1. **Logistik:** Mengurangi 1 sachet masker `BC-MSK-001` dari stok Studio Bandung (`ST-BDG`).
    2. **Akuntansi:** Memposting jurnal beban pokok (Debit COGS Rp40.000 / Kredit Persediaan Rp40.000).
    3. **Penjualan:** Menerbitkan faktur jasa pelanggan sebesar Rp333.000 (Rp300.000 tarif jasa + PPN 11% Rp33.000).
  - Terbukti pada pengujian **T-33** dan dijamin anti-duplikasi (*idempotent*) pada **T-34**.

### Pertanyaan 5: "Bagaimana sistem menjamin keamanan data antar departemen (RBAC)?"
* **Jawaban:**
  - Kami mengimplementasikan kontrol akses peran (SR-19) untuk 6 peran pengguna (*Sales, Warehouse, Finance, Studio, Purchasing, Owner*).
  - Pengguna dengan peran selain Keuangan (misalnya *Warehouse*) diblokir dengan **HTTP 403 Forbidden** saat mencoba mengakses Buku Besar (*General Ledger*) atau Dashboard Finansial. Setiap percobaan akses ilegal otomatis tercatat ke dalam tabel `audit_logs`.
  - Terbukti pada pengujian **T-02** dan **T-36**.

---

## 3. Matriks Pembagian Peran Anggota Tim (RACI)

Jika penguji menanyakan kontribusi masing-masing anggota kelompok:

| Anggota | Peran Tim | Fokus Tanggung Jawab | Modul & Bukti Fungsional |
| :--- | :--- | :--- | :--- |
| **Anggota 1** | **Product Owner & Business Analyst** | Menganalisis kebutuhan stakeholder, menyusun Decision Log (DL-01 s/d DL-19), mendesain alur CRM dan Penjualan. | F-03, F-05, F-06, F-07, F-11;<br>Test: T-03 s/d T-10 |
| **Anggota 2** | **Lead Backend & AI Integration** | Merancang skema database (22 tabel), migrasi SQLite, *Domain Services*, integrasi pergerakan stok, dan pengadaan barang. | F-08, F-09, F-12, F-13, F-17;<br>Test: T-11 s/d T-14, T-19 s/d T-22, T-27 s/d T-28 |
| **Anggota 3** | **UI/UX & Frontend Architect** | Mengembangkan antarmuka Odoo 17 Web (19 Layar Blade), App Switcher, Profile Switcher Karyawan PT Arunika, dan modul Studio. | F-01, F-02, F-19, F-20;<br>Test: T-01, T-02, T-31 s/d T-34 |
| **Anggota 4** | **QA & Financial Accounting Lead** | Membangun mesin jurnal otomatis, Three-Way Matching, General Ledger, Dashboard Eksekutif, dan eksekusi 62 Automated Test Cases. | F-10, F-14 s/d F-16, F-18, F-21 s/d F-25;<br>Test: T-15 s/d T-18, T-23 s/d T-26, T-29 s/d T-30, T-35 s/d T-38 |

---

## 4. Panduan Live Demo di Depan Penguji

Urutan demonstrasi yang paling memukau dan logis saat presentasi:

1. **Buka Beranda (`/`) sebagai Sales (Kevin Mahendra):**
   - Perlihatkan keaslian desain Odoo 17: App Switcher di kiri atas dan Profil Karyawan di kanan atas.
   - Tunjukkan saldo awal kas bank **Rp50.000.000** dan data bersih baseline.
2. **Kredit Limit & Penjualan (`/customers` & `/sales`):**
   - Buka `C002` (Nadira Putri) yang memiliki limit Rp500.000. Coba pesan 3 unit toner &rarr; Tunjukkan penolakan otomatis dari sistem.
   - Buka `C001` (Ayu Prameswari). Buat pesanan 4 unit sabun cuci muka &rarr; Tunjukkan reservasi stok di Gudang Jakarta.
3. **Pengiriman Parsial & Backorder (`/inventory/delivery`):**
   - Beralih ke **Wahyu Saputra (Warehouse)**.
   - Buka Delivery Order `WH/OUT/00007`, kirim 2 unit saja.
   - Tunjukkan stok berkurang menjadi 13 dan sistem otomatis menerbitkan dokumen **Backorder** untuk 2 unit sisanya.
4. **Three-Way Matching Pengadaan (`/purchase` & `/purchase/receipts`):**
   - Beralih ke **Taufik Rahman (Purchasing)** lalu buat PO 5 unit toner.
   - Di penerimaan barang, terima 4 unit dan tolak 1 unit cacat ke karantina.
   - Beralih ke **Nanda Kusuma (Finance)** & buka `/purchase/bills` &rarr; Tunjukkan sistem hanya menagihkan 4 unit diterima.
5. **Studio Treatment Completion (`/studio/treatments`):**
   - Beralih ke **Laras Kirana (Studio)**.
   - Selesaikan *Glow Facial*. Perlihatkan bahwa 1 sachet masker di Bandung langsung berkurang, jurnal COGS Rp40.000 terposting, dan faktur jasa Rp333.000 terbit otomatis.
6. **Buku Besar & Uji Keamanan RBAC (`/accounting/general-ledger`):**
   - Buka Buku Besar sebagai Finance &rarr; Tunjukkan keseimbangan total Debit dan Kredit.
   - Coba ganti user menjadi **Wahyu (Warehouse)** dan muat ulang halaman &rarr; Tunjukkan layar **HTTP 403 Forbidden** dan log pencatatan audit.
