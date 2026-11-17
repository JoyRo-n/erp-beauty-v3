# SYSTEM OVERVIEW, PURPOSE, BACKGROUND & STEP-BY-STEP GUIDE
**Project:** PT Arunika Beauty Nusantara — NusaGlow ERP System  
**Architecture:** Odoo 17.0 Web Pattern · Laravel 12 · SQLite (AES-256-CBC Encrypted)

---

## 1. Purpose of the System (Tujuan Sistem)

### English
The purpose of the **PT Arunika Beauty Nusantara (NusaGlow) ERP System** is to unify two distinct operational business lines—**Skincare Product Retail** and **Beauty Studio Treatment Services**—into a single, real-time, audit-ready Enterprise Resource Planning (ERP) platform. Specifically, the system is designed to:
1. **Synchronize Physical Inventory and Financial Accounting:** Ensure every stock movement (warehouse receipt, sales delivery, internal transfer, return, or studio mask consumption) automatically triggers an accurate, balanced double-entry accounting journal (`Debit = Credit`).
2. **Enforce Strict Internal Controls & Segregation of Duties (RBAC):** Isolate each division (**Sales, Warehouse, Purchasing, Studio, Finance, Owner, and Master Admin**) so staff can only view and operate their own division's screens, while allowing controlled, time-bound **Cross-Division Access Requests** approved by the **Master Admin**.
3. **Protect Sensitive Corporate & Customer Data:** Automatically encrypt sensitive business records at rest in the database using **AES-256-CBC encryption** (`EncryptedString` and `EncryptedArray` casts) so raw database inspection cannot expose confidential data.

### Bahasa Indonesia
Tujuan dari **Sistem ERP PT Arunika Beauty Nusantara (NusaGlow)** adalah menyatukan dua lini bisnis utama perusahaan—**Penjualan Retail Produk Skincare** dan **Layanan Perawatan Studio Kecantikan**—ke dalam satu platform Enterprise Resource Planning (ERP) yang terintegrasi secara *real-time* dan siap audit. Secara spesifik, sistem ini bertujuan untuk:
1. **Menyelaraskan Arus Fisik Barang (Logistik) dan Pencatatan Moneter (Akuntansi):** Memastikan setiap pergerakan stok (penerimaan barang vendor, pengiriman pesanan, transfer antargudang, retur, maupun pemakaian masker di studio) secara otomatis menghasilkan jurnal akuntansi berpasangan (*double-entry*) yang seimbang (`Debit = Credit`).
2. **Menegakkan Pengendalian Internal & Pemisahan Tugas (RBAC):** Mengisolasi setiap divisi (**Sales, Warehouse, Purchasing, Studio, Finance, Owner, dan Master Admin**) agar staf hanya dapat melihat dan mengakses modul divisinya sendiri, disertai mekanisme **Pop-Up Request Access** lintas divisi yang diawasi dan disetujui oleh **Master Admin**.
3. **Melindungi Keamanan Data Perusahaan & Pelanggan:** Mengenkripsi data sensitif yang masuk ke dalam database secara otomatis menggunakan **enkripsi AES-256-CBC** sehingga data mentah di database tetap terlindungi.

---

## 2. Background (Latar Belakang)

### English
**PT Arunika Beauty Nusantara** operates a central distribution warehouse in Jakarta (`WH-JKT`) and a beauty clinic/studio branch in Bandung (`ST-BDG`). Before implementing this ERP system, the company faced several operational bottlenecks common to growing retail-and-service beauty enterprises:
* **Disconnected Silos Between Divisions:** The Sales team confirmed orders without knowing real-time *Available* stock in `WH-JKT`, leading to over-promising. Meanwhile, the Bandung Studio (`ST-BDG`) consumed skincare consumables (such as sheet masks `BC-MSK-001`) during facial treatments without deducting inventory or recording Cost of Goods Sold (COGS).
* **Premature & Inaccurate Billing:** Invoices were previously generated upon order confirmation rather than actual delivery, causing financial misstatements whenever partial deliveries or backorders occurred.
* **Uncontrolled Vendor Billing:** Defective raw materials or skincare bottles rejected by Quality Control (QC) at the warehouse were still accidentally billed by vendors and paid by Finance due to the lack of Three-Way Matching.
* **Data Privacy & Role Leakage Risks:** Without strict session isolation and database encryption, staff in one division could see other divisions' internal structures, financial margins, or customer balances.

### Bahasa Indonesia
**PT Arunika Beauty Nusantara** mengelola gudang distribusi pusat di Jakarta (`WH-JKT`) serta cabang klinik/studio kecantikan di Bandung (`ST-BDG`). Sebelum menggunakan sistem ERP terpadu, perusahaan menghadapi berbagai kendala operasional:
* **Silo Data Antar-Divisi:** Tim Sales sering mengonfirmasi pesanan tanpa mengetahui sisa stok *Available* secara *real-time* di `WH-JKT`. Di sisi lain, Studio Bandung (`ST-BDG`) menggunakan bahan habis pakai (seperti masker wajah `BC-MSK-001`) saat layanan *facial* tanpa memotong stok gudang ataupun mencatat Beban Pokok Penjualan (COGS).
* **Penagihan Fiktif / Prematur:** Faktur pelanggan dibuat sebelum barang dikirim, sehingga menimbulkan selisih laporan keuangan ketika terjadi pengiriman parsial (*partial delivery*).
* **Tagihan Vendor Tidak Terkendali:** Barang cacat yang ditolak saat pemeriksaan kualitas (*Quality Control*) di gudang tetap tertagih oleh vendor karena belum adanya sistem *Three-Way Matching*.
* **Risiko Kebocoran Data Antar-Divisi:** Tanpa isolasi sesi yang ketat dan enkripsi database, staf suatu divisi dapat melihat data internal divisi lain, margin keuntungan, atau buku besar perusahaan.

---

## 3. Why Is It Needed? (Mengapa Sistem Ini Sangat Dibutuhkan?)

| Problem Without This System (Masalah Tanpa Sistem) | How NusaGlow ERP Solves It (Solusi Sistem NusaGlow ERP) | Reference Rule |
| :--- | :--- | :--- |
| **1. Bad Debts (Piutang Macet)**<br>Customers order beyond their credit capacity. | **Automated Credit Limit & Exposure Guard:** Calculates `Opening AR + Unpaid Invoices + Unbilled Orders` and blocks orders that exceed a customer's credit limit or when Discounts > 15%. | `SR-07`, `SR-08`, `DL-04`, `DL-05` |
| **2. Phantom & Negative Stock (Stok Fiktif / Negatif)**<br>Warehouse ships more items than physically available. | **3-Tier Stock Control (`On Hand`, `Reserved`, `Available`):** Prevents over-reservation and automatically generates a **Backorder** document on partial deliveries. | `SR-03`, `SR-04`, `DL-02`, `DL-06` |
| **3. Paying for Defective Goods (Membayar Barang Reject)**<br>Finance pays for damaged vendor deliveries. | **Three-Way Matching & Quarantine Routing:** Rejected items go to a non-valued Quarantine location (`Rp0`), and Vendor Bills are strictly capped at `Accepted Quantity`. | `SR-05`, `SR-06`, `DL-03` |
| **4. Unrecorded Studio Consumables & Unqualified Staff**<br>Treatments performed by uncertified staff or without deducting stock. | **Skill Matrix Validation & Atomic Studio Completion:** Blocks therapists who lack the required skill (e.g., *Facial*), and automatically deducts 1 mask, posts COGS (`Rp40,000`), and generates the service invoice (`Rp333,000`) upon completion. | `SR-17`, `SR-18`, `DL-14`, `DL-15` |
| **5. Unauthorized Cross-Division Snooping & Data Breaches**<br>Staff accessing financial ledgers or other divisions' records. | **Strict RBAC Isolation + Key Pop-Up Request Access + AES-256 Encryption:** Non-Admin roles cannot see other divisions or switch accounts, unauthorized URLs return `HTTP 403` (logged to `audit_logs`), temporary access requires Master Admin approval, and database records are AES-256 encrypted. | `SR-19`, `SR-20`, `DL-09`, `DL-16` |

---

## 4. Use & Positiveness / Key Benefits (Kegunaan & Dampak Positif)

### A. Operational & Financial Positiveness (Manfaat Operasional & Finansial)
1. **100% Accounting Integrity (`Debit = Credit`):** Every invoice, payment, delivery, purchase receipt, return, and studio service automatically posts balanced journal entries to the General Ledger with idempotency protection (preventing duplicate double-clicks).
2. **Real-Time Executive Visibility for Owner:** The **Owner** (`owner@gmail.com`) has a dedicated executive financial view (**Accounting & Invoicing — 10 Screens**) to monitor Revenue, COGS, Gross Profit, Cash/Bank (`Rp50,000,000` baseline), Accounts Receivable (AR), and Accounts Payable (AP) with direct drill-down links to source documents.
3. **Centralized Governance for Master Admin:** The **Master Admin** (`admin@gmail.com`) has full oversight across all **7 Apps & 19 Screens**, an Employee Role & Permission Matrix, and the **Key Icon Pop-Up Approval Modal** to grant or revoke temporary cross-division access (1 hour, 4 hours, 24 hours, 3 days, 7 days, or until deleted).
4. **Zero-Friction Cross-Division Collaboration:** When a division (e.g., Sales) legitimately needs temporary access to another module (e.g., Inventory Delivery Order to answer a customer inquiry), they don't need to share passwords—they simply click the **Key Icon (`fa-key`)** in the top header and submit a request in the centered pop-up modal.
5. **End-to-End Security & Auditability:**
   - **Session Isolation:** Logging in as `Sales` hides all other divisions' names and accounts.
   - **Database Encryption at Rest:** Sensitive text and array fields across 15 core models are encrypted using Laravel's `Crypt` (AES-256-CBC) payload format (`eyJpdiI6...`).
   - **Complete Audit Trail:** Every state change and access denial is recorded in `audit_logs`.

---

## 5. Step-by-Step Guide to Using the System (Panduan Penggunaan Langkah demi Langkah)

### Step 0: Starting & Logging Into the System (Membuka & Login ke Sistem)
1. Ensure the server is running (`php artisan serve --host=127.0.0.1 --port=8000`) and open `http://127.0.0.1:8000` in your browser.
2. Log in at `/login` using the credentials of the division you want to operate:
   - **Sales:** `sales@gmail.com` / `sales123`
   - **Warehouse:** `warehouse@gmail.com` / `warehouse123`
   - **Purchasing:** `purchasing@gmail.com` / `purchasing123`
   - **Studio:** `studio@gmail.com` / `studio123`
   - **Finance:** `finance@gmail.com` / `finance123`
   - **Owner (Executive Finance & Profit):** `owner@gmail.com` / `owner123`
   - **Master Admin (Superuser & Access Approver):** `admin@gmail.com` / `admin123`

---

### Workflow 1: Order-to-Cash (Sales &rarr; Warehouse &rarr; Finance)
1. **Convert Lead & Create Sales Order (Role: `Sales`):**
   - Go to **CRM Leads (`/crm`)** and convert `LEAD-0001` into an Opportunity.
   - Go to **Customers (`/customers`)** to verify customer credit limits (e.g., `C001` Ayu Prameswari has `Rp2,000,000` limit; `C002` Nadira Putri has `Rp500,000` limit).
   - Go to **Sales Orders (`/sales`)**, create an order for `C001` (e.g., 4 units of `BC-CLN-100`), and click **Confirm**. The system validates the credit limit, caps discounts at `15%`, and reserves stock in `WH-JKT`.
2. **Validate Delivery & Backorder (Role: `Warehouse`):**
   - Log in as `Warehouse` and open **Delivery Orders (`/inventory/delivery`)**.
   - Input the delivered quantity (e.g., `2` out of `4` units) and click **Validate**.
   - The system deducts `On Hand` stock, posts the **COGS Journal** (`Dr 5010 COGS / Cr 1200 Inventory`), and automatically creates a **Backorder (`-BO`)** for the remaining `2` units.
3. **Post Customer Invoice & Receive Payment (Role: `Finance`):**
   - Log in as `Finance` and open **Customer Invoices (`/sales/invoices`)**.
   - Create and **Post** the invoice for the `2` delivered units (including `11%` VAT).
   - Open **Customer Payments (`/sales/payments`)** and record full or partial payment into **1010 Bank**.
   - *(Optional Return)* If sealed goods are returned via **Product Returns (`/sales/returns`)**, the system restores stock, reverses COGS, and issues a **Credit Note**.

---

### Workflow 2: Procure-to-Pay with Three-Way Matching (Purchasing &rarr; Warehouse &rarr; Finance)
1. **Create & Confirm Purchase Order (Role: `Purchasing`):**
   - Log in as `Purchasing` and open **Purchase Orders (`/purchase`)**.
   - Create an RFQ (e.g., `5` units of `BC-TON-100` from vendor `V001`) and click **Confirm**. Once confirmed, the PO is locked against vendor or price tampering.
2. **Receive Goods & QC Inspection (Role: `Warehouse`):**
   - Log in as `Warehouse` and open **Purchase Receipts (`/purchase/receipts`)**.
   - Record QC results: e.g., **Accepted = 4 units**, **Rejected = 1 unit**, and validate.
   - Only the `4` accepted units enter valued stock (`WH-JKT`); the `1` rejected unit is routed to the Quarantine area at `Rp0` valuation.
3. **Three-Way Match Vendor Bill & Settlement (Role: `Finance`):**
   - Log in as `Finance` and open **Vendor Bills (`/purchase/bills`)**.
   - Create and post the Vendor Bill—the system enforces **Three-Way Matching**, allowing billing for at most the `4` accepted units.
   - Go to **Vendor Payments (`/purchase/payments`)** to settle the open Accounts Payable (`AP`) balance from the Bank account.

---

### Workflow 3: Beauty Studio Treatment & Internal Stock Transfer (Warehouse &rarr; Studio)
1. **Transfer Stock to Bandung Studio (Role: `Warehouse`):**
   - Open **Internal Transfers (`/inventory/transfers`)** to move skincare supplies from `WH-JKT` to `ST-BDG` without altering total company inventory valuation.
2. **Assign Certified Therapist & Complete Treatment (Role: `Studio`):**
   - Log in as `Studio` and open **Employees (`/employees`)** to verify therapist skill certifications (e.g., `E001` Citra Dewi holds the certified *Facial* skill).
   - Open **Studio Treatments (`/studio/treatments`)**, assign `E001` to a *Glow Facial* session, and click **Complete**.
   - The system atomically deducts `1` sheet mask (`BC-MSK-001`) from `ST-BDG`, posts `Rp40,000` COGS, and posts a `Rp333,000` service invoice (`Rp300,000` + `11%` VAT).

---

### Workflow 4: Cross-Division Access Request via Key Pop-Up Modal (Staff &rarr; Master Admin)
1. **Submit a Request (Any Staff Role, e.g., `Sales`):**
   - Click the **Key Icon (`fa-key`)** in the top-right header bar.
   - A **Centered Pop-Up Modal** opens. On the left column (**Forward Request to Master Admin**), select the **Target Module** you need temporary access to and enter the **Reason / Related Document** (e.g., *"Need to inspect Delivery WH/OUT/00006 for customer inquiry"*).
   - Click **Forward Request to Master Admin**. The request appears in the right column with status `Pending Master Admin`.
2. **Review & Approve Request (Role: `Master Admin` — `admin@gmail.com`):**
   - Log in as `admin@gmail.com`. Notice the notification badge on the **Key Icon (`fa-key`)** in the top header.
   - Click the **Key Icon** to open the **Master Admin: Access Approval** centered pop-up modal.
   - In the left column (**Pending Staff Requests**), choose the access duration (`1 Hour`, `4 Hours`, `8 Hours`, `24 Hours`, `3 Days`, `7 Days`, or `Until Deleted`), add an optional review note, and click **Approve** (or **Reject**).
3. **Use or Revoke Temporary Access:**
   - When the staff member (`Sales`) opens the Key Pop-Up Modal or their App Switcher, the approved module is now unlocked with an **Open** button and expiration timer.
   - At any time, **Master Admin** can revoke individual access grants (`Delete`) or clear all active grants (`Delete All`) from the right column of the Key Pop-Up Modal.

