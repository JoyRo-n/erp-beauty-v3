# ROLE ASSIGNMENT SHEET & TEAM AGREEMENT
**Proyek:** ERP PT Arunika Beauty Nusantara (NusaGlow)  
**Mata Kuliah:** Perencanaan Sumber Daya Perusahaan  
**Periode:** 4 Minggu  
**Checkpoint:** Minggu 1  

---

# BAGIAN 1: ROLE ASSIGNMENT SHEET

Dokumen ini membagi kepemilikan modul secara adil dan end-to-end (kebutuhan, build, pengujian, dan demo) untuk 4 mahasiswa.

| Anggota | Nama & NIM | Peran Fungsional | Modul yang Dimiliki (*Ownership*) | Tanggung Jawab Dominan | Fitur yang Dibangun (*F*) | Pengujian yang Dieksekusi (*T*) | Rubrik Penilaian Dominan |
| :---: | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **M1** | *(Isi Nama Kamu)* | **Product Owner (PO) / Project Manager (PM) / Business Analyst (BA)** | **Sales & CRM** | Prioritas fitur, analisis acceptance criteria, alur Order to Cash (O2C), penyusunan Decision Log, koordinasi checkpoint tim. | `F-03`, `F-05`, `F-06`, `F-07`, `F-11` | `T-03`, `T-04`, `T-05`, `T-06`, `T-07`, `T-08`, `T-09`, `T-10` | Kebenaran Aturan Bisnis (*Business Rules*) & Decision Defense |
| **M2** | *(Isi Nama Teman 2)* | **Lead AI Developer** | **Purchase & Inventory** | Integrasi pergerakan barang, penomoran urut dokumen, review Pull Request (PR) kode, dan merge repository. | `F-08`, `F-09`, `F-12`, `F-13`, `F-17` | `T-11`, `T-12`, `T-13`, `T-14`, `T-19`, `T-20`, `T-21`, `T-22`, `T-27`, `T-28` | Integritas Data & Logika Pergerakan Stok |
| **M3** | *(Isi Nama Teman 3)* | **UI/UX & Frontend Lead** | **Employees & Operasi Studio** | Konsistensi antarmuka Odoo Web 17, pembatasan visibilitas menu sesuai peran (*Role Visibility*), validasi skill karyawan dan treatment salon. | `F-01`, `F-02`, `F-19`, `F-20` | `T-01`, `T-02`, `T-31`, `T-32`, `T-33`, `T-34` | Kesetiaan Desain UI Odoo & Validasi Skill |
| **M4** | *(Isi Nama Teman 4)* | **QA & Deployment Lead** | **Accounting & Invoicing** | Siklus jurnal umum (General Ledger), eksekusi rangkaian test otomatis/manual, defect log, migrasi seed data awal, dan hosting publik. | `F-10`, `F-14`, `F-15`, `F-16`, `F-18`, `F-21`, `F-22`, `F-23`, `F-25` | `T-15`, `T-16`, `T-17`, `T-18`, `T-23`, `T-24`, `T-25`, `T-26`, `T-29`, `T-30`, `T-35`, `T-36`, `T-37`, `T-38` | Kebenaran Finansial, Audit Pengujian, & Keandalan Deployment |

---

# BAGIAN 2: RACI MATRIX

* **A** = Accountable (Satu pemilik keputusan akhir)
* **R** = Responsible (Pelaksana tugas)
* **C** = Consulted (Pihak yang dimintai masukan)
* **I** = Informed (Pihak yang diberi tahu hasil keputusan)

| Aktivitas | M1 (PO / BA) | M2 (Lead Dev) | M3 (UI/UX) | M4 (QA / Deploy) |
| :--- | :---: | :---: | :---: | :---: |
| **Analisis Kebutuhan & Acceptance Criteria** | **A / R** | C | C | C |
| **Model Data & Dataset Baseline** | **A** | **R** | C | C |
| **Implementasi Wireframe (19 Layar S-xx)** | C | C | **A / R** | R |
| **Module Build (Kode Aplikasi)** | A | **R** | R | R |
| **Integrasi & Uji Aturan Bisnis (SR-01–SR-23)** | C | R | C | **A / R** |
| **Penyusunan & Pemeliharaan Decision Log** | **A / R** | C | C | C |
| **Deployment Aplikasi & Kredensial Role** | I | C | C | **A / R** |
| **Pembuatan Video Demo & Presentasi Final** | **A** | R | R | R |

---

# BAGIAN 3: TEAM AGREEMENT (TATA KELOLA KOLABORASI)

### 1. Ritme Rapat & Sinkronisasi
* **Weekly Sync:** Diadakan seminggu sekali setiap hari **Sabtu pukul 19.30 WIB** (via Google Meet / Discord) untuk mereviu target mingguan dan persiapan checkpoint.
* **Daily Async Update:** Setiap anggota wajib menuliskan 3 poin singkat di grup WhatsApp sebelum pukul 21.00 WIB:
  1. Apa yang sudah dikerjakan hari ini?
  2. Apa yang akan dikerjakan besok?
  3. Apakah ada kendala (*blocker*)?

### 2. Standar Kolaborasi Kode (Git Workflow)
* Cabang utama (`main`) dilindungi (*protected*). Kode yang masuk ke `main` harus melalui *Pull Request* (PR).
* Anggota 2 (Lead Dev) bertindak sebagai *reviewer* kode teknis, dan Anggota 1 (PO) memastikan fitur yang dibuat sudah memenuhi *Acceptance Criteria* bisnis.
* Setiap anggota wajib memiliki riwayat *commit* nyata yang mencerminkan pengerjaan modulnya masing-masing.

### 3. Protokol Eskalasi Keterlambatan & Penyelesaian Defect
* Batas waktu respons pesan grup terkait proyek maksimal **24 jam**.
* Jika salah satu modul mengalami kendala logika atau keterlambatan lebih dari 2 hari dari jadwal target mingguan:
  * Anggota yang bersangkutan wajib melapor ke Anggota 1 (PO).
  * Tim menjadwalkan sesi *pair programming / AI prompt debugging* bersama untuk menyelesaikan hambatan tersebut.
* Defect yang ditemukan oleh Anggota 4 (QA) dicatat di `DEFECT_LOG.md` dan wajib diselesaikan oleh pemilik modul sebelum lanjut ke modul berikutnya.

### 4. Kebijakan Penggunaan AI (AI Transparency)
* Pemanfaatan AI Coding (Antigravity / LLM lainnya) sangat dianjurkan dan diperbolehkan.
* Seluruh *prompt* kunci, logika perbaikan aturan bisnis, dan catatan "keluaran AI yang tampilannya benar tapi logikanya salah" wajib didokumentasikan di `AI_USAGE_LOG.md` untuk persiapan bahan presentasi slide ke-5.
