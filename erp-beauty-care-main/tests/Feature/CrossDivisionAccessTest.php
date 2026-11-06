<?php

namespace Tests\Feature;

use App\Models\AccessGrant;
use App\Models\AppNotification;
use App\Models\User;
use Database\Seeders\BaselineSeeder;
use Tests\TestCase;

class CrossDivisionAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (new BaselineSeeder())->run();
    }

    protected function as(string $role)
    {
        return $this->actingAs(User::where('role', $role)->firstOrFail());
    }

    public function test_admin_and_owner_can_open_every_division_screen(): void
    {
        $urls = [
            '/customers', '/products', '/crm', '/sales', '/inventory/stock', '/inventory/delivery',
            '/invoicing/customer', '/accounting/payments', '/purchase/orders', '/inventory/receipts',
            '/accounting/bills', '/accounting/general-ledger', '/inventory/transfers', '/sales/returns',
            '/employees', '/studio/treatments', '/accounting/dashboard', '/accounting/vendor-payments',
        ];

        foreach (['Admin', 'Owner'] as $role) {
            foreach ($urls as $url) {
                $this->as($role)->get($url)->assertStatus(200, "{$role} should open {$url}");
            }
        }
    }

    public function test_admin_can_post_documents_of_other_divisions(): void
    {
        // Admin must not be blocked by the Finance-only posting guards
        $response = $this->as('Admin')->post('/invoicing/customer/INV-NOT-EXIST/post');
        $this->assertStringNotContainsString('Hanya Finance', (string) session('error'));

        $response = $this->as('Admin')->post('/accounting/bills/BILL-NOT-EXIST/post');
        $this->assertStringNotContainsString('Hanya Finance', (string) session('error'));
    }

    public function test_team_without_grant_is_denied_then_allowed_after_grant_and_denied_after_revoke(): void
    {
        $this->as('Warehouse')->get('/accounting/general-ledger')->assertStatus(403);

        $this->as('Admin')->post('/access/grant', [
            'grantee_role' => 'Warehouse',
            'apps' => ['accounting'],
            'note' => 'Bantu rekonsiliasi',
        ])->assertRedirect(route('s01.home'));

        $this->assertDatabaseHas('access_grants', ['grantee_role' => 'Warehouse', 'app_key' => 'accounting']);

        // Warehouse can now open the accounting module and sees it in its app list
        $this->as('Warehouse')->get('/accounting/general-ledger')->assertStatus(200);
        $this->as('Warehouse')->get('/')->assertStatus(200)->assertSee('Accounting');

        // But still not the modules that were not granted
        $this->as('Warehouse')->get('/sales')->assertStatus(403);

        $grant = AccessGrant::first();
        $this->as('Admin')->post("/access/{$grant->id}/revoke")->assertRedirect(route('s01.home'));
        $this->as('Warehouse')->get('/accounting/general-ledger')->assertStatus(403);
    }

    public function test_granting_notifies_receiving_team_and_owning_division(): void
    {
        $this->as('Admin')->post('/access/grant', [
            'grantee_role' => 'Warehouse',
            'apps' => ['accounting'],
        ]);

        $this->assertTrue(AppNotification::where('recipient_role', 'Warehouse')->where('title', 'like', 'Akses baru%')->exists());
        $this->assertTrue(AppNotification::where('recipient_role', 'Finance')->where('title', 'like', 'Tim Warehouse%')->exists());

        // Bell shows the notification to the receiving team
        $this->as('Warehouse')->get('/')->assertSee('Akses baru: Accounting');

        $this->as('Warehouse')->post('/notifications/read');
        $this->assertEquals(0, AppNotification::where('recipient_role', 'Warehouse')->whereNull('read_at')->count());
    }

    public function test_only_admin_or_owner_can_manage_access(): void
    {
        $this->as('Sales')->post('/access/grant', [
            'grantee_role' => 'Sales',
            'apps' => ['accounting'],
        ])->assertStatus(403);

        $this->assertEquals(0, AccessGrant::count());

        $this->as('Owner')->post('/access/grant', [
            'grantee_role' => 'Sales',
            'apps' => ['accounting'],
        ])->assertRedirect(route('s01.home'));
        $this->assertEquals(1, AccessGrant::count());
    }

    public function test_granting_an_already_owned_module_is_skipped(): void
    {
        $this->as('Admin')->post('/access/grant', [
            'grantee_role' => 'Finance',
            'apps' => ['accounting'],
        ]);

        $this->assertEquals(0, AccessGrant::count());
        $this->assertEquals(0, AppNotification::count());
    }

    public function test_seven_official_reference_applications_match_handout(): void
    {
        $apps = \App\Services\RoleAccessService::getAllApps();
        $this->assertCount(7, $apps);
        $this->assertEquals(
            ['sales', 'crm', 'purchase', 'inventory', 'accounting', 'invoicing', 'employee'],
            array_keys($apps)
        );
    }

    public function test_redundant_and_unguarded_access_eliminated_per_handout(): void
    {
        // S-11 (Receipts) & S-03 (Products): Purchasing must NOT have redundant access
        $this->as('Purchasing')->get('/inventory/receipts')->assertStatus(403);
        $this->as('Purchasing')->get('/products')->assertStatus(403);

        // S-07 validateDelivery & S-11 validateReceipt must reject non-Warehouse roles
        foreach (['Sales', 'Finance', 'Purchasing', 'Studio'] as $role) {
            $this->as($role)->post('/inventory/delivery/WH-OUT-00007/validate')->assertStatus(403);
            $this->as($role)->post('/inventory/receipts/WH-IN-00007/validate')->assertStatus(403);
        }

        // Reset baseline & Scenario Demo runner restricted to Finance / Owner / Admin
        foreach (['Sales', 'Warehouse', 'Purchasing', 'Studio'] as $role) {
            $this->as($role)->post('/reset-baseline')->assertStatus(403);
            $this->as($role)->post('/demo/run-scenario')->assertStatus(403);
        }

        // S-06: Sales & Warehouse can view stock overview
        $this->as('Sales')->get('/inventory/stock')->assertStatus(200);
        $this->as('Sales')->get('/products')->assertStatus(200);

        // S-10: Warehouse can view POs, but cannot create POs
        $this->as('Warehouse')->get('/purchase/orders')->assertStatus(200);
        $this->as('Warehouse')->post('/purchase/orders', [
            'vendor_id' => 'V01',
            'variant_sku' => 'BC-TON-100',
            'qty_ordered' => 5,
        ])->assertStatus(403);

        // S-12: Purchasing can view Vendor Bills (3-way match), but cannot post Vendor Bills
        $this->as('Purchasing')->get('/accounting/bills')->assertStatus(200);
        $this->as('Purchasing')->post('/accounting/bills/BILL-ANY/post')->assertStatus(403);

        // S-15: Warehouse & Finance can open Returns; Sales cannot execute physical return/Credit Note posting
        $this->as('Warehouse')->get('/sales/returns')->assertStatus(200);
        $this->as('Finance')->get('/sales/returns')->assertStatus(200);
        $this->as('Sales')->post('/sales/returns', [
            'origin_invoice_number' => 'INV/2026/00007',
            'variant_sku' => 'BC-CLN-100',
            'return_qty' => 1,
            'inspection_status' => 'Sealed / Accepted',
        ])->assertStatus(403);

        // S-17: Finance can view Treatments to trace Service Invoices, but only Studio can schedule/complete them
        $this->as('Finance')->get('/studio/treatments')->assertStatus(200);
        $this->as('Finance')->post('/studio/treatments', [
            'customer_id' => 'C004',
            'employee_id' => 'E001',
        ])->assertStatus(403);
        $this->as('Finance')->post('/studio/treatments/TRT-TEST/complete')->assertStatus(403);
    }

    public function test_end_to_end_http_workflow_auto_generates_draft_invoice_and_three_way_matched_bill(): void
    {
        // 1. O2C via HTTP: Sales creates SO -> Warehouse validates partial DO (2 of 4) -> Finance posts generated Draft Invoice
        $this->as('Sales')->post('/sales', [
            'customer_id' => 'C001',
            'variant_sku' => 'BC-CLN-100',
            'qty' => 4,
            'discount_percent' => 0,
        ])->assertRedirect();

        $this->as('Warehouse')->post(route('s07.validate', 'WH/OUT/00007'), [
            'done_qty' => ['BC-CLN-100' => 2],
        ])->assertRedirect();

        $draftInv = \App\Models\CustomerInvoice::where('delivery_order_number', 'WH/OUT/00007')->firstOrFail();
        $this->assertEquals('Draft', $draftInv->status);
        $this->assertEquals(266400, (float) $draftInv->total_amount);

        $this->as('Finance')->post(route('s08.post', $draftInv->invoice_number))->assertRedirect();
        $this->assertEquals('Posted', $draftInv->fresh()->status);

        // 2. P2P via HTTP: Purchasing creates PO (5 x BC-TON-100) -> Warehouse validates Receipt (4 accepted, 1 rejected) -> Finance posts 3-way matched Bill
        $this->as('Purchasing')->post('/purchase/orders', [
            'vendor_id' => 'V01',
            'variant_sku' => 'BC-TON-100',
            'qty_ordered' => 5,
        ])->assertRedirect();

        $this->as('Warehouse')->post(route('s11.validate', 'WH/IN/00007'), [
            'accepted_BC-TON-100' => 4,
            'rejected_BC-TON-100' => 1,
        ])->assertRedirect();

        $draftBill = \App\Models\VendorBill::where('receipt_number', 'WH/IN/00007')->firstOrFail();
        $this->assertEquals('Draft', $draftBill->status);
        $this->assertEquals(310800, (float) $draftBill->total_amount);

        $this->as('Finance')->post(route('s12.post', $draftBill->bill_number))->assertRedirect();
        $this->assertEquals('Posted', $draftBill->fresh()->status);

        // 3. Studio via HTTP: Studio completes TRT/2026/00007 with slash in ID
        $this->as('Studio')->post(route('s17.complete', 'TRT/2026/00007'))->assertRedirect();
        $this->assertDatabaseHas('beauty_treatments', ['treatment_number' => 'TRT/2026/00007', 'status' => 'Done']);
    }

    public function test_home_chart_allocation_by_role(): void
    {
        // Admin, Owner, Finance see Struktur Likuiditas & Aset only
        foreach (['Admin', 'Owner', 'Finance'] as $role) {
            $res = $this->as($role)->get('/');
            $res->assertOk();
            $res->assertSee('Struktur Likuiditas & Aset');
            $res->assertSee('id="financeChart"', false);
            $res->assertDontSee('Distribusi Ketersediaan Persediaan Produk');
            $res->assertDontSee('id="inventoryChart"', false);
        }

        // Sales and Warehouse see Distribusi Ketersediaan Persediaan Produk only
        foreach (['Sales', 'Warehouse'] as $role) {
            $res = $this->as($role)->get('/');
            $res->assertOk();
            $res->assertSee('Distribusi Ketersediaan Persediaan Produk');
            $res->assertSee('id="inventoryChart"', false);
            $res->assertDontSee('Struktur Likuiditas & Aset');
            $res->assertDontSee('id="financeChart"', false);
        }

        // Purchasing and Studio see neither chart
        foreach (['Purchasing', 'Studio'] as $role) {
            $res = $this->as($role)->get('/');
            $res->assertOk();
            $res->assertDontSee('Struktur Likuiditas & Aset');
            $res->assertDontSee('Distribusi Ketersediaan Persediaan Produk');
            $res->assertDontSee('id="financeChart"', false);
            $res->assertDontSee('id="inventoryChart"', false);
        }
    }

    public function test_staff_forwards_access_request_and_requires_master_admin_approval(): void
    {
        // 1. Sales has no access to General Ledger initially
        $this->as('Sales')->get('/accounting/general-ledger')->assertStatus(403);

        // 2. Sales opens side panel and forwards request for 'accounting' to Master Admin
        $this->as('Sales')->from('/')->post('/access/requests', [
            'app_key' => 'accounting',
            'reason' => 'Need to reconcile customer invoice ledger items',
        ])->assertRedirect('/');

        $req = \App\Models\AccessRequest::firstOrFail();
        $this->assertEquals('Pending', $req->status);
        $this->assertEquals('Sales', $req->requester_role);

        // 3. Access is STILL denied while Pending Master Admin approval, and Master Admin got notified
        $this->as('Sales')->get('/accounting/general-ledger')->assertStatus(403);
        $this->assertTrue(AppNotification::where('recipient_role', 'Admin')->where('title', 'like', 'Permintaan akses%')->exists());

        // 4. Non-Master-Admin roles (including staff and Owner) cannot approve staff requests
        $this->as('Sales')->post("/access/requests/{$req->id}/approve")->assertStatus(403);
        $this->as('Owner')->post("/access/requests/{$req->id}/approve")->assertStatus(403);
        $this->assertEquals('Pending', $req->fresh()->status);

        // 5. Master Admin approves the request -> AccessGrant created, Sales now has access, and divisions notified
        $this->as('Admin')->from('/')->post("/access/requests/{$req->id}/approve", [
            'review_note' => 'Approved for month-end check',
        ])->assertRedirect('/');

        $this->assertEquals('Approved', $req->fresh()->status);
        $this->assertDatabaseHas('access_grants', ['grantee_role' => 'Sales', 'app_key' => 'accounting']);
        $this->as('Sales')->get('/accounting/general-ledger')->assertStatus(200);
        $this->assertTrue(AppNotification::where('recipient_role', 'Sales')->where('title', 'like', 'Permintaan disetujui%')->exists());
        $this->assertTrue(AppNotification::where('recipient_role', 'Finance')->where('title', 'like', 'Tim Sales mengakses%')->exists());

        // 6. Reject flow: Warehouse requests 'purchase', Master Admin rejects -> still 403
        $this->as('Warehouse')->from('/')->post('/access/requests', [
            'app_key' => 'purchase',
            'reason' => 'Want to create PO',
        ])->assertRedirect('/');

        $req2 = \App\Models\AccessRequest::where('requester_role', 'Warehouse')->firstOrFail();
        $this->as('Admin')->from('/')->post("/access/requests/{$req2->id}/reject", [
            'review_note' => 'PO creation stays with Purchasing',
        ])->assertRedirect('/');

        $this->assertEquals('Rejected', $req2->fresh()->status);
        $this->assertDatabaseMissing('access_grants', ['grantee_role' => 'Warehouse', 'app_key' => 'purchase']);
    }
}

