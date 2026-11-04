# LAPORAN STATUS PENYELESAIAN TUGAS ANGGOTA 1
**Peran:** Product Owner (PO) / Project Manager (PM) / Business Analyst (BA)  
**Kepemilikan Modul:** Sales & CRM  
**Nama Proyek:** ERP PT Arunika Beauty Nusantara (NusaGlow)  
**Status Keseluruhan:** **100% SIAP & SELESAI (CHECKPOINT MINGGU 1 & MINGGU 2 MODUL SALES)**

---

## 1. Tanggung Jawab Dokumen Manajerial (Wajib Minggu 1)

| Dokumen Wajib | Lokasi Berkas | Status | Detail Keterangan |
| :--- | :--- | :---: | :--- |
| **Role Assignment Sheet** | `ROLE_ASSIGNMENT_AND_TEAM_AGREEMENT.md` | ✅ **LENGKAP** | Memetakan 4 mahasiswa, kepemilikan modul, RACI Matrix, fitur $F\text{-xx}$, dan tes $T\text{-xx}$. |
| **Team Agreement** | `ROLE_ASSIGNMENT_AND_TEAM_AGREEMENT.md` | ✅ **LENGKAP** | Jadwal rapat mingguan, daily update, standar Git workflow, dan protokol eskalasi defect. |
| **Decision Log (SR-01 s/d SR-23)** | `DECISION_LOG.md` | ✅ **LENGKAP** | Berisi matriks *Decision Defense* (5 konflik utama, siapa yang dirugikan, trade-off, mitigasi, dan evidence $T$). |
| **Spesifikasi Database & Baseline Seed** | `SEED_DATASET_AND_DATA_MODEL.md` | ✅ **LENGKAP** | 6 pelanggan, 15 produk kosmetik, 4 vendor, stok 396 unit, dan neraca kas awal Rp50.000.000. |

---

## 2. Tanggung Jawab Fitur Teknis Modul Sales & CRM

| Kode Fitur | Nama Fitur & Layar | Aturan Bisnis Terkait | Status Implementasi |
| :---: | :--- | :--- | :---: |
| **`F-03`** | Form Customer & Credit Terms (`S-02`) | **SR-08:** Master kontak menampilkan limit kredit, payment terms, opening AR, dan sisa plafon. | ✅ **BERFUNGSI PENUH** |
| **`F-06`** | Quotation hingga Sales Order (`S-05`) | **SR-02, SR-12:** Dokumen bersiklus (Draft $\rightarrow$ Confirmed), nomor urut unik, reservasi stok di WH-JKT. | ✅ **BERFUNGSI PENUH** |
| **`F-07`** | Diskon & Price Lock | **SR-07:** Batas keras diskon per baris maksimal 15%. Harga & diskon terkunci permanen setelah Confirmed. | ✅ **BERFUNGSI PENUH** |
| **`F-11`** | Pemeriksaan Credit Limit / Exposure | **SR-08:** Menolak pesanan secara otomatis jika $\text{Unpaid AR} + \text{Order Baru} > \text{Credit Limit}$. | ✅ **BERFUNGSI PENUH** |
| **`F-23`** | Pajak PPN 11% & Pembulatan Rupiah | **SR-16:** PPN efektif 11% dibulatkan ke rupiah terdekat per baris produk. | ✅ **BERFUNGSI PENUH** |

---

## 3. Matriks Hasil Pengujian Resmi (*Evidence T-xx*)

| Kode Uji | Skenario Pengujian | Hasil yang Diharapkan | Hasil Aktual Sistem Kita | Status |
| :---: | :--- | :--- | :--- | :---: |
| **`T-03`** | Buka pelanggan C001 (Ayu Prameswari) di Form Customer (S-02) | Limit Rp2.000.000, Opening AR Rp0, Net 14, Total AR perusahaan Rp4.440.000. | Ditampilkan akurat pada form Profil Lengkap & kartu pelanggan dengan badge hijau "Kredit Aman". | ✅ **PASS** |
| **`T-04`** | C002 (Nadira Putri) membuat pesanan melebihi batas kredit | Sistem menolak konfirmasi karena Total Exposure ($Rp754.800$) melebihi Credit Limit ($Rp500.000$). | Muncul kotak merah penolakan SR-08: *"Konfirmasi Ditolak! Total Exposure melampaui limit..."* dan dicatat di Audit Log. | ✅ **PASS** |
| **`T-09`** | C001 membeli 2 BC-CLN-100 tanpa diskon dari WH-JKT | DPP Rp240.000, PPN Rp26.400, Total Rp266.400, status Confirmed, stok Reserved +2. | Berhasil dikonfirmasi menjadi Sales Order resmi, Delivery Order `WH/OUT/00010` otomatis terbit, Price Lock aktif. | ✅ **PASS** |
| **`T-10`** | Sales mencoba memasukkan diskon 20% pada produk | Sistem menolak konfirmasi karena batas wewenang diskon Sales maksimal 15% (SR-07). | Pop-up peringatan muncul di form dan alert merah controller memblokir penyimpanan: *"Aturan SR-07 Dilanggar!"*. | ✅ **PASS** |
