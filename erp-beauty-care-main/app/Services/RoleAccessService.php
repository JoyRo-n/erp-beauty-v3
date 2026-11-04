<?php

namespace App\Services;

class RoleAccessService
{
    /**
     * Role profile specifications and metadata
     */
    public static function getProfiles(): array
    {
        return [
            'Admin' => [
                'name' => 'Master Administrator',
                'email' => 'admin@gmail.com',
                'role_title' => 'Master Super Admin',
                'avatar_color' => 'bg-slate-800',
                'badge_color' => 'bg-slate-100 text-slate-800 border-slate-300',
                'dept' => 'Divisi Sistem & Seluruh Modul',
                'access' => 'Semua Modul & Akses Penuh (Superuser)',
                'description' => 'Memiliki hak akses menyeluruh ke seluruh sistem ERP, dapat mengaudit dan menginspeksi hak akses setiap staf.',
            ],
            'Owner' => [
                'name' => 'Ratih Widya',
                'email' => 'owner@gmail.com',
                'role_title' => 'Managing Director / Owner',
                'avatar_color' => 'bg-indigo-600',
                'badge_color' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                'dept' => 'Kantor Eksekutif / Manajemen',
                'access' => 'Semua Modul & Laporan Eksekutif',
                'description' => 'Akses menyeluruh ke seluruh dokumen bisnis dan metrik KPI eksekutif perusahaan.',
            ],
            'Sales' => [
                'name' => 'Kevin Mahendra',
                'email' => 'sales@gmail.com',
                'role_title' => 'Sales Representative',
                'avatar_color' => 'bg-purple-600',
                'badge_color' => 'bg-purple-100 text-purple-800 border-purple-300',
                'dept' => 'Divisi Penjualan & CRM',
                'access' => 'Sales, CRM Pipeline, Pelanggan & Retur',
                'description' => 'Mengelola prospek pelanggan, penawaran harga (Quotation), Sales Order, dan retur kosmetik.',
            ],
            'Warehouse' => [
                'name' => 'Wahyu Saputra',
                'email' => 'warehouse@gmail.com',
                'role_title' => 'Warehouse Specialist',
                'avatar_color' => 'bg-amber-600',
                'badge_color' => 'bg-amber-100 text-amber-800 border-amber-300',
                'dept' => 'Divisi Gudang & Logistik',
                'access' => 'Stok, Pengiriman, Penerimaan & Transfer',
                'description' => 'Mengelola fisik persediaan, reservasi stok, validasi surat jalan (DO), penerimaan barang, dan mutasi antarlokasi.',
            ],
            'Finance' => [
                'name' => 'Nanda Kusuma',
                'email' => 'finance@gmail.com',
                'role_title' => 'Finance & Accounting Head',
                'avatar_color' => 'bg-emerald-600',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'dept' => 'Divisi Keuangan & Akuntansi',
                'access' => 'Buku Besar, Faktur, Tagihan & Pembayaran',
                'description' => 'Mengelola General Ledger, pembukuan jurnal debit-kredit, faktur pelanggan, tagihan vendor (3-way match), dan kas/bank.',
            ],
            'Purchasing' => [
                'name' => 'Taufik Rahman',
                'email' => 'purchasing@gmail.com',
                'role_title' => 'Purchasing Officer',
                'avatar_color' => 'bg-blue-600',
                'badge_color' => 'bg-blue-100 text-blue-800 border-blue-300',
                'dept' => 'Divisi Pengadaan & Vendor',
                'access' => 'Purchase Orders, RFQ & Katalog Vendor',
                'description' => 'Mengelola permintaan penawaran (RFQ), konfirmasi pesanan pembelian (PO), dan evaluasi harga beli pemasok.',
            ],
            'Studio' => [
                'name' => 'Laras Kirana',
                'email' => 'studio@gmail.com',
                'role_title' => 'Studio Therapist & Operations',
                'avatar_color' => 'bg-rose-600',
                'badge_color' => 'bg-rose-100 text-rose-800 border-rose-300',
                'dept' => 'Operasional Glow Studio Bandung',
                'access' => 'Data Karyawan, Skill & Treatment Salon',
                'description' => 'Mengelola jadwal terapis salon, penugasan keahlian facial, dan penyelesaian layanan kecantikan konsumen.',
            ],
        ];
    }

    /**
     * Get profile for active role
     */
    public static function getProfile(string $role): array
    {
        $profiles = self::getProfiles();
        return $profiles[$role] ?? $profiles['Sales'];
    }

    /**
     * Master definition of all applications
     */
    public static function getAllApps(): array
    {
        return [
            'sales' => [
                'id' => 'sales',
                'title' => 'Sales',
                'subtitle' => 'Quotations & Sales Orders',
                'route' => 's05.sales',
                'icon' => 'fa-solid fa-chart-line',
                'color' => 'purple',
                'bg_color' => 'bg-purple-100',
                'text_color' => 'text-purple-700',
                'hover_border' => 'hover:border-purple-500',
                'division' => 'Sales & CRM',
            ],
            'crm' => [
                'id' => 'crm',
                'title' => 'CRM',
                'subtitle' => 'Lead Pipeline & Conversion',
                'route' => 's04.crm',
                'icon' => 'fa-solid fa-bullseye',
                'color' => 'indigo',
                'bg_color' => 'bg-indigo-100',
                'text_color' => 'text-indigo-700',
                'hover_border' => 'hover:border-indigo-500',
                'division' => 'Sales & CRM',
            ],
            'purchase' => [
                'id' => 'purchase',
                'title' => 'Purchase',
                'subtitle' => 'PO, RFQ & Vendors',
                'route' => 's10.purchase',
                'icon' => 'fa-solid fa-cart-shopping',
                'color' => 'blue',
                'bg_color' => 'bg-blue-100',
                'text_color' => 'text-blue-700',
                'hover_border' => 'hover:border-blue-500',
                'division' => 'Purchasing',
            ],
            'inventory' => [
                'id' => 'inventory',
                'title' => 'Inventory',
                'subtitle' => 'Stock, Deliveries & Receipts',
                'route' => 's06.stock',
                'icon' => 'fa-solid fa-warehouse',
                'color' => 'amber',
                'bg_color' => 'bg-amber-100',
                'text_color' => 'text-amber-700',
                'hover_border' => 'hover:border-amber-500',
                'division' => 'Warehouse',
            ],
            'accounting' => [
                'id' => 'accounting',
                'title' => 'Accounting',
                'subtitle' => 'General Ledger & Journals',
                'route' => 's13.ledger',
                'icon' => 'fa-solid fa-book-journal-whills',
                'color' => 'emerald',
                'bg_color' => 'bg-emerald-100',
                'text_color' => 'text-emerald-700',
                'hover_border' => 'hover:border-emerald-500',
                'division' => 'Finance & Accounting',
            ],
            'invoicing' => [
                'id' => 'invoicing',
                'title' => 'Invoicing',
                'subtitle' => 'Customer Invoices & Bills',
                'route' => 's08.invoice',
                'icon' => 'fa-solid fa-file-invoice-dollar',
                'color' => 'teal',
                'bg_color' => 'bg-teal-100',
                'text_color' => 'text-teal-700',
                'hover_border' => 'hover:border-teal-500',
                'division' => 'Finance & Accounting',
            ],
            'employee' => [
                'id' => 'employee',
                'title' => 'Employees & Studio',
                'subtitle' => 'Therapist Skills & Treatments',
                'route' => 's16.employee',
                'icon' => 'fa-solid fa-users',
                'color' => 'rose',
                'bg_color' => 'bg-rose-100',
                'text_color' => 'text-rose-700',
                'hover_border' => 'hover:border-rose-500',
                'division' => 'Studio Bandung',
            ],
            'dashboard' => [
                'id' => 'dashboard',
                'title' => 'Executive Dashboard',
                'subtitle' => 'Scenario KPIs & Financial Audit',
                'route' => 's18.dashboard',
                'icon' => 'fa-solid fa-gauge-high',
                'color' => 'violet',
                'bg_color' => 'bg-violet-100',
                'text_color' => 'text-violet-700',
                'hover_border' => 'hover:border-violet-500',
                'division' => 'Executive & Finance',
            ],
        ];
    }

    /**
     * Get accessible applications filtered strictly by role/division
     * Admin and Owner have access to ALL applications.
     */
    public static function getAccessibleApps(string $role): array
    {
        $allApps = self::getAllApps();

        if (in_array($role, ['Admin', 'Owner'])) {
            return $allApps;
        }

        $roleAppMap = [
            'Sales' => ['sales', 'crm'],
            'Warehouse' => ['inventory'],
            'Finance' => ['accounting', 'invoicing', 'dashboard'],
            'Purchasing' => ['purchase'],
            'Studio' => ['employee'],
        ];

        $allowedKeys = $roleAppMap[$role] ?? ['sales'];
        $filtered = [];
        foreach ($allowedKeys as $key) {
            if (isset($allApps[$key])) {
                $filtered[$key] = $allApps[$key];
            }
        }
        return $filtered;
    }

    /**
     * Master definition of all 19 verification screen shortcuts (S-01 to S-19)
     */
    public static function getAllShortcuts(): array
    {
        return [
            's01' => ['id' => 's01', 'code' => 'S-01', 'title' => 'Beranda & App Switcher', 'route' => 's01.home', 'roles' => ['All']],
            's02' => ['id' => 's02', 'code' => 'S-02', 'title' => 'Form Customer & Credit', 'route' => 's02.customer', 'roles' => ['Sales', 'Finance', 'Admin', 'Owner']],
            's03' => ['id' => 's03', 'code' => 'S-03', 'title' => 'Product & Variant SKU', 'route' => 's03.product', 'roles' => ['Sales', 'Warehouse', 'Purchasing', 'Admin', 'Owner']],
            's04' => ['id' => 's04', 'code' => 'S-04', 'title' => 'CRM Pipeline Kanban', 'route' => 's04.crm', 'roles' => ['Sales', 'Admin', 'Owner']],
            's05' => ['id' => 's05', 'code' => 'S-05', 'title' => 'Quotation & Sales Order', 'route' => 's05.sales', 'roles' => ['Sales', 'Admin', 'Owner']],
            's06' => ['id' => 's06', 'code' => 'S-06', 'title' => 'Stock & Reservation', 'route' => 's06.stock', 'roles' => ['Warehouse', 'Admin', 'Owner']],
            's07' => ['id' => 's07', 'code' => 'S-07', 'title' => 'Delivery & Backorder', 'route' => 's07.delivery', 'roles' => ['Warehouse', 'Admin', 'Owner']],
            's08' => ['id' => 's08', 'code' => 'S-08', 'title' => 'Customer Invoice', 'route' => 's08.invoice', 'roles' => ['Finance', 'Admin', 'Owner']],
            's09' => ['id' => 's09', 'code' => 'S-09', 'title' => 'Register Payment', 'route' => 's09.payment', 'roles' => ['Finance', 'Admin', 'Owner']],
            's10' => ['id' => 's10', 'code' => 'S-10', 'title' => 'RFQ & Purchase Order', 'route' => 's10.purchase', 'roles' => ['Purchasing', 'Admin', 'Owner']],
            's11' => ['id' => 's11', 'code' => 'S-11', 'title' => 'Receipt & Quality Accept', 'route' => 's11.receipt', 'roles' => ['Warehouse', 'Purchasing', 'Admin', 'Owner']],
            's12' => ['id' => 's12', 'code' => 'S-12', 'title' => 'Vendor Bill (3-Way Match)', 'route' => 's12.bill', 'roles' => ['Finance', 'Admin', 'Owner']],
            's13' => ['id' => 's13', 'code' => 'S-13', 'title' => 'General Ledger & Entries', 'route' => 's13.ledger', 'roles' => ['Finance', 'Admin', 'Owner']],
            's14' => ['id' => 's14', 'code' => 'S-14', 'title' => 'Internal Stock Transfer', 'route' => 's14.transfer', 'roles' => ['Warehouse', 'Admin', 'Owner']],
            's15' => ['id' => 's15', 'code' => 'S-15', 'title' => 'Return & Credit Note', 'route' => 's15.return', 'roles' => ['Sales', 'Admin', 'Owner']],
            's16' => ['id' => 's16', 'code' => 'S-16', 'title' => 'Employee & Skill Matrix', 'route' => 's16.employee', 'roles' => ['Studio', 'Admin', 'Owner']],
            's17' => ['id' => 's17', 'code' => 'S-17', 'title' => 'Treatment Completion', 'route' => 's17.treatment', 'roles' => ['Studio', 'Admin', 'Owner']],
            's18' => ['id' => 's18', 'code' => 'S-18', 'title' => 'Executive Dashboard', 'route' => 's18.dashboard', 'roles' => ['Finance', 'Admin', 'Owner']],
            's19' => ['id' => 's19', 'code' => 'S-19', 'title' => 'Vendor Payment & AP', 'route' => 's19.vendor_payment', 'roles' => ['Finance', 'Admin', 'Owner']],
        ];
    }

    /**
     * Get filtered shortcuts: Only the necessary shortcuts for each specific staff/division.
     * Admin and Owner get all 19 shortcuts.
     */
    public static function getAccessibleShortcuts(string $role): array
    {
        $all = self::getAllShortcuts();
        if (in_array($role, ['Admin', 'Owner'])) {
            return $all;
        }

        return array_filter($all, function ($sc) use ($role) {
            return in_array('All', $sc['roles']) || in_array($role, $sc['roles']);
        });
    }

    /**
     * Matrix of all staff for the Main Admin to inspect and locate permissions
     */
    public static function getAdminStaffLocatorMatrix(): array
    {
        $profiles = self::getProfiles();
        $matrix = [];

        foreach ($profiles as $roleKey => $prof) {
            $apps = self::getAccessibleApps($roleKey);
            $shortcuts = self::getAccessibleShortcuts($roleKey);

            $matrix[$roleKey] = [
                'role' => $roleKey,
                'name' => $prof['name'],
                'email' => $prof['email'],
                'role_title' => $prof['role_title'],
                'dept' => $prof['dept'],
                'avatar_color' => $prof['avatar_color'],
                'badge_color' => $prof['badge_color'],
                'allowed_apps_count' => count($apps),
                'allowed_shortcuts_count' => count($shortcuts),
                'allowed_apps' => array_column($apps, 'title'),
                'allowed_shortcuts' => array_column($shortcuts, 'code'),
            ];
        }

        return $matrix;
    }
}
