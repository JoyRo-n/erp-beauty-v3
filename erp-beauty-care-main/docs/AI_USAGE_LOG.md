# AI USAGE & TRANSPARENCY LOG
**Proyek:** ERP Web App — PT Arunika Beauty Nusantara (NusaGlow)  
**Dokumen:** Log Penggunaan AI Coding & Refleksi Rekayasa Perintah (*Prompt Engineering*)  
**Mata Kuliah:** Perencanaan Sumber Daya Perusahaan  
**Bahan Khusus:** Materi Paparan Presentasi Akhir (Slide ke-5: *AI Transparency & Hallucination Defense*)  

---

## 1. Ikhtisar Kebijakan Penggunaan AI

Dalam proyek pengembangan ERP PT Arunika Beauty Nusantara, tim memanfaatkan asisten pemrograman kecerdasan buatan (*Agentic Coding Assistant*) untuk mempercepat pembuatan modul, scaffolding basis data, perancangan antarmuka Odoo 17 Blade, dan pembuatan rangkaian pengujian otomatis PHPUnit.

Sesuai dengan kesepakatan tata kelola tim (*Team Agreement Bagian 4*), seluruh penggunaan AI dipandu dengan prinsip:
1. **Verifikasi Manusia Mutlak (*Human-in-the-Loop*):** Tidak ada kode yang dimasukkan ke cabang utama tanpa verifikasi terhadap aturan bisnis Odoo dan regulasi keuangan Indonesia (PSAK & PPN 11%).
2. **Pengujian Berbasis Bukti (*Test-Driven Integrity*):** Setiap kode yang digenerasi AI wajib divalidasi oleh 62 acceptance test cases otomatis (T-01 s/d T-38).
3. **Dokumentasi Ilusi AI (*AI Hallucinations / Logic Errors*):** Mencatat secara transparan kejadian di mana keluaran AI menghasilkan kode yang sepintas tampak benar di layar, namun cacat logika bisnis saat diverifikasi.

---

## 2. Matriks Refleksi Kritis: "Keluaran AI yang Tampilannya Benar tapi Logikanya Salah"

Bagian ini merupakan bukti orisinalitas analisis tim dan bahan utama untuk menjawab pertanyaan dosen penguji pada Slide ke-5 presentasi.

| No | Modul | Situasi Kode dari AI | Kenapa Tampilannya Terlihat Benar? | Cacat Logika Bisnis yang Ditemukan Tim (*The Flaw*) | Tindakan Koreksi Tim (*Correction*) |
| :---: | :--- | :--- | :--- | :--- | :--- |
| **1** | **Invoicing & O2C (DL-01)** | AI membuat faktur pelanggan langsung saat Sales Order dikonfirmasi. | Nilai total pesanan tertera rapi di layar invoice dan status dokumen langsung berubah menjadi *Invoiced*. | **Melanggar prinsip PSAK & Matching Principle.** Jika barang dikirim bertahap (*partial delivery*), perusahaan mencatat pendapatan fiktif atas barang yang belum diserahkan. | Mengunci pembuatan invoice hanya dari Delivery Order yang sudah divalidasi kuantitas *Done*-nya (`delivered_qty`). Terbukti di **T-15 & T-16**. |
| **2** | **Procure to Pay (DL-03)** | AI membuat Vendor Bill mencakup seluruh 5 unit pesanan PO, meskipun 1 unit berstatus reject karantina. | Total tagihan sama persis dengan PO awal dan formulir bill tampak lengkap tanpa error sintaks. | **Kerugian finansial perseroan.** Vendor Bill yang mencakup barang cacat akan menambah Utang Usaha (Akun 2100) dan mengakui barang rusak sebagai aset dinilai. | Membatasi Vendor Bill hanya boleh ditagihkan maksimum sebesar kuantitas `Accepted` (4 unit). Barang reject dialihkan ke karantina dengan valuasi Rp0. Terbukti di **T-21 & T-24**. |
| **3** | **Perpajakan (DL-13 / SR-16)** | AI menghitung PPN dengan cara: `round(Total DPP Faktur * 0.11)`. | Angka total pajak di bagian bawah nota terlihat rapi tanpa pecahan desimal. | **Ketidaksesuaian per baris (*Line Item Variance*).** Regulasi perpajakan faktur mewajibkan penghitungan pajak dilakukan per baris produk dengan pembulatan rupiah terdekat, sehingga total faktur adalah jumlah dari pembulatan tiap baris. | Menulis fungsi deterministik `calculateLineTax(unitPrice, qty, discount)` yang menghitung PPN 11% dibulatkan ke rupiah terdekat per item. Terbukti di **T-09**. |
| **4** | **Inventory Reservation (DL-02)** | AI memblokir konfirmasi Sales Order jika stok gudang kurang dari jumlah yang dipesan. | Mencegah stok menjadi minus di tabel `stock_quants`. | **Menghilangkan peluang penjualan (*Lost Sales*).** Sales Manager menginginkan pesanan tetap dapat dikonfirmasi untuk menangkap permintaan pasar, sementara pemenuhannya menunggu pasokan. | Mengizinkan SO berstatus *Confirmed*, namun membatasi surat jalan fisik (*Delivery Order*) hanya sejumlah kuantitas *Available*. Sisa barang otomatis dijadikan *Backorder*. Terbukti di **T-11 & T-12**. |
| **5** | **Idempotensi Transaksi (DL-08)** | AI membuat tombol posting jurnal dan validasi delivery yang mengeksekusi operasi penambahan mutasi setiap kali tombol ditekan. | Form bekerja normal dan data tersimpan dengan status *Success* pada percobaan pertama. | **Kerentanan klik ganda (*Double Execution*).** Jika staf menekan tombol "Validate" atau "Post" dua kali, stok terpotong ganda dan jurnal keuangan tercatat dua kali. | Menambahkan proteksi status dokumen dan pemeriksaan primary key unik: jika dokumen sudah berstatus `Done` atau `Posted`, eksekusi ulang langsung diabaikan (*idempotent*). Terbukti di **T-14, T-26, & T-34**. |

---

## 3. Log Prompt Kunci yang Digunakan Tim

### Prompt 1: Penegakan Batas Kredit (Credit Exposure Enforcement)
```text
"Buatkan implementasi CreditLimitService pada Laravel untuk model Contact. 
Formula batas kredit wajib menghitung: 
Credit Exposure = Opening AR + Unpaid Posted Invoices + Unbilled Confirmed Sales Orders.
Sistem wajib menolak konfirmasi Sales Order jika Exposure + Nilai Order Baru > Credit Limit pelanggan, 
serta mencatat insiden penolakan ke audit_logs (SR-08 / DL-05)."
```
* **Hasil:** Dihasilkan service `CreditLimitService::calculateExposure()` dan `validateOrderExposure()` yang lolos pengujian `T-03` dan `T-04`.

### Prompt 2: Three-Way Matching & Karantina Non-Valued
```text
"Rancang alur validasi penerimaan barang (Purchase Receipt) di mana dari 5 unit yang dipesan, 
staf gudang menerima 4 unit layak pakai dan menolak 1 unit cacat ke karantina. 
Barang karantina wajib dicatat di stock_moves dengan nilai valuasi Rp0 (non-valued). 
Kemudian buat validasi Vendor Bill agar finance hanya dapat menerbitkan tagihan untuk 4 unit yang berstatus accepted (DL-03)."
```
* **Hasil:** Berhasil mengisolasi barang reject dari akun persediaan dinilai (1200) dan mencegah over-billing pada pengujian `T-21` dan `T-24`.

### Prompt 3: Integrasi Treatment Salon ke Logistik & Finansial
```text
"Saat terapis menyelesaikan treatment Glow Facial (TRT/2026/00007), sistem harus mengeksekusi 
3 proses dalam satu transaksi atomik (DB::transaction):
1. Mengurangi 1 sachet BC-MSK-001 dari lokasi ST-BDG.
2. Memposting jurnal beban pemakaian masker: Dr Treatment Consumables 5010 (Rp40.000) / Cr Stock Valuation 1200 (Rp40.000).
3. Menerbitkan Customer Invoice jasa sebesar Rp333.000 (tarif Rp300.000 + PPN 11% Rp33.000).
Aksi ini wajib bersifat idempotent (tidak boleh memotong masker ganda jika diklik ulang)."
```
* **Hasil:** Menghasilkan `TreatmentService::completeTreatment()` yang terintegrasi penuh pada pengujian `T-33` dan dijamin aman pada `T-34`.

---

## 4. Kesimpulan untuk Presentasi Tim

Pemanfaatan AI bukan sekadar meminta AI menuliskan seluruh kode aplikasi, melainkan **sebagai alat akselerasi di mana mahasiswa berperan sebagai arsitek sistem, penentu aturan bisnis, dan auditor pengujian data**. Kegagalan AI dalam memahami nuansa akuntansi dan logistik membuktikan pentingnya penguasaan teori ERP dalam merancang sistem perusahaan nyata.

