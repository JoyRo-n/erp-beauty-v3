<?php

namespace App\Services;

use App\Models\AccessGrant;
use Illuminate\Support\Facades\Schema;

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
                'avatar_color' => 'bg-rose-500',
                'badge_color' => 'bg-pink-100 text-rose-900 border-pink-300',
                'dept' => 'Divisi Sistem & Seluruh Modul',
                'access' => 'Semua Modul & Akses Penuh (Superuser)',
                'description' => 'Memiliki hak akses menyeluruh ke seluruh sistem ERP, dapat mengaudit dan menginspeksi hak akses setiap staf.',
            ],
            'Owner' => [
                'name' => 'Ratih Widya',
                'email' => 'owner@gmail.com',
                'role_title' => 'Managing Director / Owner',
                'avatar_color' => 'bg-fuchsia-500',
                'badge_color' => 'bg-fuchsia-100 text-fuchsia-800 border-fuchsia-300',
                'dept' => 'Kantor Eksekutif / Manajemen',
                'access' => 'Keuangan Bisnis, Profitabilitas, Kas, Piutang/Utang & Buku Besar',
                'description' => 'Memantau kesehatan finansial bisnis, laporan laba rugi (profit & COGS), arus kas/bank, eksposur kredit, faktur, tagihan, dan buku besar.',
            ],
            'Sales' => [
                'name' => 'Kevin Mahendra',
                'email' => 'sales@gmail.com',
                'role_title' => 'Sales Representative',
                'avatar_color' => 'bg-pink-500',
                'badge_color' => 'bg-pink-100 text-rose-800 border-pink-300',
                'dept' => 'Divisi Penjualan & CRM',
                'access' => 'Sales, CRM, Pelanggan, Produk & Cek Stok',
                'description' => 'Mengelola prospek pelanggan, penawaran harga (Quotation), Sales Order, dan pemantauan stok tersedia.',
            ],
            'Warehouse' => [
                'name' => 'Wahyu Saputra',
                'email' => 'warehouse@gmail.com',
                'role_title' => 'Warehouse Specialist',
                'avatar_color' => 'bg-amber-600',
                'badge_color' => 'bg-amber-100 text-amber-800 border-amber-300',
                'dept' => 'Divisi Gudang & Logistik',
                'access' => 'Stok, Pengiriman, Penerimaan, Transfer & Retur',
                'description' => 'Mengelola fisik persediaan, reservasi stok, validasi surat jalan (DO), penerimaan barang, mutasi lokasi, dan inspeksi retur.',
            ],
            'Finance' => [
                'name' => 'Nanda Kusuma',
                'email' => 'finance@gmail.com',
                'role_title' => 'Finance & Accounting Head',
                'avatar_color' => 'bg-emerald-600',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'dept' => 'Divisi Keuangan & Akuntansi',
                'access' => 'Buku Besar, Faktur, Tagihan, Pembayaran & Dashboard',
                'description' => 'Mengelola General Ledger, pembukuan jurnal debit-kredit, faktur pelanggan, tagihan vendor (3-way match), kas/bank, dan audit.',
            ],
            'Purchasing' => [
                'name' => 'Taufik Rahman',
                'email' => 'purchasing@gmail.com',
                'role_title' => 'Purchasing Officer',
                'avatar_color' => 'bg-blue-600',
                'badge_color' => 'bg-blue-100 text-blue-800 border-blue-300',
                'dept' => 'Divisi Pengadaan & Vendor',
                'access' => 'Purchase Orders, RFQ & Tinjauan Tagihan Vendor',
                'description' => 'Mengelola permintaan penawaran (RFQ), konfirmasi pesanan pembelian (PO), dan pemantauan tiga arah tagihan pemasok.',
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
                'color' => 'pink',
                'bg_color' => 'bg-pink-100',
                'text_color' => 'text-rose-700',
                'hover_border' => 'hover:border-pink-400',
                'division' => 'Sales & CRM',
            ],
            'crm' => [
                'id' => 'crm',
                'title' => 'CRM',
                'subtitle' => 'Lead Pipeline & Conversion',
                'route' => 's04.crm',
                'icon' => 'fa-solid fa-bullseye',
                'color' => 'fuchsia',
                'bg_color' => 'bg-fuchsia-100',
                'text_color' => 'text-fuchsia-700',
                'hover_border' => 'hover:border-fuchsia-400',
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
                'subtitle' => 'General Ledger, Payments & Dashboard',
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
                'subtitle' => 'Customer Invoices & Vendor Bills',
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
                'title' => 'Employees',
                'subtitle' => 'Therapist Skills & Treatments',
                'route' => 's16.employee',
                'icon' => 'fa-solid fa-users',
                'color' => 'rose',
                'bg_color' => 'bg-rose-100',
                'text_color' => 'text-rose-700',
                'hover_border' => 'hover:border-rose-500',
                'division' => 'Studio Bandung',
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

        if (self::isSuperRole($role)) {
            return $allApps;
        }

        $allowedKeys = array_unique(array_merge(self::getBaseAppKeys($role), self::getGrantedAppKeys($role)));
        $filtered = [];
        foreach ($allApps as $key => $app) {
            if (in_array($key, $allowedKeys, true)) {
                $filtered[$key] = $app;
            }
        }
        return $filtered;
    }

    /**
     * Only Master Admin ('Admin') has unrestricted superuser access to every division.
     * Owner is strictly scoped to business financial & profit visibility.
     */
    public static function isSuperRole(?string $role): bool
    {
        return $role === 'Admin';
    }

    /**
     * Default (built-in) application keys of each division team.
     */
    public static function getBaseAppKeys(string $role): array
    {
        $roleAppMap = [
            'Owner' => ['accounting', 'invoicing'],
            'Sales' => ['sales', 'crm'],
            'Warehouse' => ['inventory'],
            'Finance' => ['accounting', 'invoicing'],
            'Purchasing' => ['purchase'],
            'Studio' => ['employee'],
        ];

        return $roleAppMap[$role] ?? ['sales'];
    }

    /**
     * Automatically remove expired cross-division grants so temporary access does not last forever.
     */
    public static function purgeExpiredGrants(): void
    {
        if (!Schema::hasTable('access_grants') || !Schema::hasColumn('access_grants', 'expires_at')) {
            return;
        }

        $expired = AccessGrant::whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expired->isEmpty()) {
            return;
        }

        $hasRequests = Schema::hasTable('access_requests');
        foreach ($expired as $grant) {
            if ($hasRequests) {
                \App\Models\AccessRequest::where('requester_role', $grant->grantee_role)
                    ->where('app_key', $grant->app_key)
                    ->where('status', 'Approved')
                    ->update(['status' => 'Expired']);
            }
            $grant->delete();
        }
    }

    /**
     * Application keys added on top of the default ones by Admin/Owner (cross-division grants).
     */
    public static function getGrantedAppKeys(string $role): array
    {
        if (!Schema::hasTable('access_grants')) {
            return [];
        }

        self::purgeExpiredGrants();

        return AccessGrant::where('grantee_role', $role)->pluck('app_key')->all();
    }

    /**
     * Division teams (roles) that natively own an application.
     */
    public static function getOwningRoles(string $appKey): array
    {
        $owners = [];
        foreach (['Sales', 'Warehouse', 'Finance', 'Purchasing', 'Studio'] as $role) {
            if (in_array($appKey, self::getBaseAppKeys($role), true)) {
                $owners[] = $role;
            }
        }
        return $owners;
    }

    /**
     * Feature name (as passed to authorizeRole) => application key.
     */
    public static function getFeatureAppMap(): array
    {
        return [
            'Customer & Credit' => 'sales',
            'Sales Order' => 'sales',
            'Sales Return' => 'sales',
            'CRM Pipeline' => 'crm',
            'Product & Catalog' => 'inventory',
            'Stock Overview' => 'inventory',
            'Delivery Order' => 'inventory',
            'Internal Stock Transfer' => 'inventory',
            'Purchase Receipt' => 'inventory',
            'Purchase Order' => 'purchase',
            'Customer Invoice' => 'invoicing',
            'Customer Payment' => 'invoicing',
            'Vendor Bill' => 'invoicing',
            'General Ledger' => 'accounting',
            'Vendor Payment' => 'accounting',
            'Financial Dashboard & AR' => 'accounting',
            'Employees & Skill Matrix' => 'employee',
            'Beauty Treatment' => 'employee',
        ];
    }

    /**
     * Shortcut screen id => application key.
     */
    public static function getShortcutAppMap(): array
    {
        return [
            's02' => 'sales', 's03' => 'inventory', 's04' => 'crm', 's05' => 'sales',
            's06' => 'inventory', 's07' => 'inventory', 's08' => 'invoicing', 's09' => 'invoicing',
            's10' => 'purchase', 's11' => 'inventory', 's12' => 'invoicing', 's13' => 'accounting',
            's14' => 'inventory', 's15' => 'sales', 's16' => 'employee', 's17' => 'employee',
            's18' => 'accounting', 's19' => 'accounting',
        ];
    }

    /**
     * Whether a role was granted access to the application behind a feature.
     */
    public static function hasGrantForFeature(string $role, string $featureName): bool
    {
        $appKey = self::getFeatureAppMap()[$featureName] ?? null;
        return $appKey !== null && in_array($appKey, self::getGrantedAppKeys($role), true);
    }

    /**
     * Master definition of all 19 verification screen shortcuts (S-01 to S-19)
     * Admin has full superuser access to all 19 screens.
     * Owner is strictly scoped to screens correlated with business financial, revenue, cost & profit.
     */
    public static function getAllShortcuts(): array
    {
        return [
            's01' => ['id' => 's01', 'code' => 'S-01', 'title' => 'Beranda & App Switcher', 'route' => 's01.home', 'roles' => ['All']],
            's02' => ['id' => 's02', 'code' => 'S-02', 'title' => 'Form Customer & Credit', 'route' => 's02.customer', 'roles' => ['Sales', 'Finance', 'Owner', 'Admin']],
            's03' => ['id' => 's03', 'code' => 'S-03', 'title' => 'Product & Variant SKU', 'route' => 's03.product', 'roles' => ['Sales', 'Warehouse', 'Admin']],
            's04' => ['id' => 's04', 'code' => 'S-04', 'title' => 'CRM Pipeline Kanban', 'route' => 's04.crm', 'roles' => ['Sales', 'Admin']],
            's05' => ['id' => 's05', 'code' => 'S-05', 'title' => 'Quotation & Sales Order', 'route' => 's05.sales', 'roles' => ['Sales', 'Owner', 'Admin']],
            's06' => ['id' => 's06', 'code' => 'S-06', 'title' => 'Stock & Reservation', 'route' => 's06.stock', 'roles' => ['Warehouse', 'Sales', 'Admin']],
            's07' => ['id' => 's07', 'code' => 'S-07', 'title' => 'Delivery & Backorder', 'route' => 's07.delivery', 'roles' => ['Warehouse', 'Admin']],
            's08' => ['id' => 's08', 'code' => 'S-08', 'title' => 'Customer Invoice', 'route' => 's08.invoice', 'roles' => ['Finance', 'Owner', 'Admin']],
            's09' => ['id' => 's09', 'code' => 'S-09', 'title' => 'Register Payment', 'route' => 's09.payment', 'roles' => ['Finance', 'Owner', 'Admin']],
            's10' => ['id' => 's10', 'code' => 'S-10', 'title' => 'RFQ & Purchase Order', 'route' => 's10.purchase', 'roles' => ['Purchasing', 'Warehouse', 'Admin']],
            's11' => ['id' => 's11', 'code' => 'S-11', 'title' => 'Receipt & Quality Accept', 'route' => 's11.receipt', 'roles' => ['Warehouse', 'Admin']],
            's12' => ['id' => 's12', 'code' => 'S-12', 'title' => 'Vendor Bill (3-Way Match)', 'route' => 's12.bill', 'roles' => ['Finance', 'Purchasing', 'Owner', 'Admin']],
            's13' => ['id' => 's13', 'code' => 'S-13', 'title' => 'General Ledger & Entries', 'route' => 's13.ledger', 'roles' => ['Finance', 'Owner', 'Admin']],
            's14' => ['id' => 's14', 'code' => 'S-14', 'title' => 'Internal Stock Transfer', 'route' => 's14.transfer', 'roles' => ['Warehouse', 'Admin']],
            's15' => ['id' => 's15', 'code' => 'S-15', 'title' => 'Return & Credit Note', 'route' => 's15.return', 'roles' => ['Warehouse', 'Finance', 'Sales', 'Owner', 'Admin']],
            's16' => ['id' => 's16', 'code' => 'S-16', 'title' => 'Employee & Skill Matrix', 'route' => 's16.employee', 'roles' => ['Studio', 'Admin']],
            's17' => ['id' => 's17', 'code' => 'S-17', 'title' => 'Treatment Completion', 'route' => 's17.treatment', 'roles' => ['Studio', 'Finance', 'Admin']],
            's18' => ['id' => 's18', 'code' => 'S-18', 'title' => 'Operational Dashboard & Audit', 'route' => 's18.dashboard', 'roles' => ['Finance', 'Owner', 'Admin']],
            's19' => ['id' => 's19', 'code' => 'S-19', 'title' => 'Vendor Payment & AP', 'route' => 's19.vendor_payment', 'roles' => ['Finance', 'Owner', 'Admin']],
        ];
    }

    /**
     * Get filtered shortcuts: Only the necessary shortcuts for each specific staff/division.
     * Admin and Owner get all 19 shortcuts.
     */
    public static function getAccessibleShortcuts(string $role): array
    {
        $all = self::getAllShortcuts();
        if (self::isSuperRole($role)) {
            return $all;
        }

        $grantedApps = self::getGrantedAppKeys($role);
        $shortcutApp = self::getShortcutAppMap();

        return array_filter($all, function ($sc) use ($role, $grantedApps, $shortcutApp) {
            if (in_array('All', $sc['roles']) || in_array($role, $sc['roles'])) {
                return true;
            }
            return isset($shortcutApp[$sc['id']]) && in_array($shortcutApp[$sc['id']], $grantedApps, true);
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
                'is_super' => self::isSuperRole($roleKey),
                'base_app_keys' => self::isSuperRole($roleKey) ? array_keys($apps) : self::getBaseAppKeys($roleKey),
                'granted_app_keys' => self::isSuperRole($roleKey) ? [] : self::getGrantedAppKeys($roleKey),
                'grants' => (self::isSuperRole($roleKey) || !Schema::hasTable('access_grants'))
                    ? collect()
                    : AccessGrant::where('grantee_role', $roleKey)->get(),
            ];
        }

        return $matrix;
    }
}
