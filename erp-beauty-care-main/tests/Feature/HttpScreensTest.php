<?php

namespace Tests\Feature;

use Tests\TestCase;
use Database\Seeders\BaselineSeeder;

class HttpScreensTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $seeder = new BaselineSeeder();
        $seeder->run();
    }

    public function test_s01_home_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Sales'])->get('/');
        $response->assertStatus(200);
        $response->assertSee('PT Arunika Beauty Nusantara');
        $response->assertSee('Sales');
    }

    public function test_s02_customer_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Sales'])->get('/customers');
        $response->assertStatus(200);
        $response->assertSee('Credit Limit');
        $response->assertSee('Ayu Prameswari');
    }

    public function test_s03_product_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Inventory'])->get('/products');
        $response->assertStatus(200);
        $response->assertSee('BC-LIP-001');
        $response->assertSee('Rose');
    }

    public function test_s04_crm_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Sales'])->get('/crm');
        $response->assertStatus(200);
        $response->assertSee('LEAD-0001');
    }

    public function test_s05_sales_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Sales'])->get('/sales');
        $response->assertStatus(200);
        $response->assertSee('S00006');
    }

    public function test_s06_stock_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Warehouse'])->get('/inventory/stock');
        $response->assertStatus(200);
        $response->assertSee('WH-JKT');
        $response->assertSee('ST-BDG');
    }

    public function test_s07_delivery_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Warehouse'])->get('/inventory/delivery');
        $response->assertStatus(200);
        $response->assertSee('Delivery Order');
    }

    public function test_s08_invoice_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Finance'])->get('/invoicing/customer');
        $response->assertStatus(200);
        $response->assertSee('Customer Invoice');
    }

    public function test_s09_payment_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Finance'])->get('/accounting/payments');
        $response->assertStatus(200);
        $response->assertSee('Register Customer Payment');
    }

    public function test_s10_purchase_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Purchasing'])->get('/purchase/orders');
        $response->assertStatus(200);
        $response->assertSee('PO00006');
    }

    public function test_s11_receipt_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Warehouse'])->get('/inventory/receipts');
        $response->assertStatus(200);
        $response->assertSee('Quality Acceptance');
    }

    public function test_s12_bill_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Finance'])->get('/accounting/bills');
        $response->assertStatus(200);
        $response->assertSee('Three-Way Match');
    }

    public function test_s13_general_ledger_screen_finance(): void
    {
        $response = $this->withSession(['active_role' => 'Finance'])->get('/accounting/general-ledger');
        $response->assertStatus(200);
        $response->assertSee('JE/2026/OPENING');
    }

    public function test_s13_general_ledger_screen_warehouse_denied(): void
    {
        $response = $this->withSession(['active_role' => 'Warehouse'])->get('/accounting/general-ledger');
        $response->assertStatus(403);
    }

    public function test_s14_transfer_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Warehouse'])->get('/inventory/transfers');
        $response->assertStatus(200);
        $response->assertSee('Internal Stock Transfer');
    }

    public function test_s15_return_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Sales'])->get('/sales/returns');
        $response->assertStatus(200);
        $response->assertSee('Credit Note');
    }

    public function test_s16_employee_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Studio'])->get('/employees');
        $response->assertStatus(200);
        $response->assertSee('E001');
        $response->assertSee('Sinta Maharani');
    }

    public function test_s17_treatment_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Studio'])->get('/studio/treatments');
        $response->assertStatus(200);
        $response->assertSee('Beauty Treatment Completion');
    }

    public function test_s18_dashboard_screen_finance(): void
    {
        $response = $this->withSession(['active_role' => 'Finance'])->get('/accounting/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Operational Dashboard');
    }

    public function test_s18_dashboard_screen_warehouse_denied(): void
    {
        $response = $this->withSession(['active_role' => 'Warehouse'])->get('/accounting/dashboard');
        $response->assertStatus(403);
    }

    public function test_s19_vendor_payment_screen(): void
    {
        $response = $this->withSession(['active_role' => 'Finance'])->get('/accounting/vendor-payments');
        $response->assertStatus(200);
        $response->assertSee('Vendor Payment');
    }

    public function test_s18_run_scenario_demo_action(): void
    {
        $response = $this->withSession(['active_role' => 'Finance'])->post('/demo/run-scenario', [
            'scenario' => 'all',
        ]);
        if (session('error')) {
            $this->fail("Demo run failed with error: " . session('error'));
        }
        $response->assertRedirect('/accounting/dashboard');
        $response->assertSessionHas('success');
    }

    public function test_s05_create_sales_order_action(): void
    {
        $response = $this->withSession(['active_role' => 'Sales'])->post('/sales', [
            'customer_id' => 'C001',
            'variant_sku' => 'BC-CLN-100',
            'qty' => 2,
            'discount_percent' => 5,
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_s02_create_customer_action(): void
    {
        $response = $this->withSession(['active_role' => 'Sales'])->post('/customers', [
            'name' => 'Bunga Melati',
            'type' => 'Perorangan',
            'city' => 'Bandung',
            'credit_limit' => 2500000,
            'payment_terms' => 'Net 14',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('contacts', ['name' => 'Bunga Melati']);
    }

    public function test_s03_create_product_action(): void
    {
        $response = $this->withSession(['active_role' => 'Inventory'])->post('/products', [
            'variant_sku' => 'BC-NEW-999',
            'name' => 'New Glow Cream',
            'size' => '50 ml',
            'color' => 'Putih',
            'unit' => 'jar',
            'sales_price' => 125000,
            'cost' => 60000,
            'default_vendor_id' => 'V01',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('products', ['variant_sku' => 'BC-NEW-999']);
    }

    public function test_s04_create_lead_action(): void
    {
        $response = $this->withSession(['active_role' => 'Sales'])->post('/crm', [
            'customer_id' => 'C001',
            'lead_name' => 'Corporate Package Demo',
        ]);
        $response->assertRedirect('/crm');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('crm_leads', ['name' => 'Corporate Package Demo']);
    }

    public function test_s16_create_employee_action(): void
    {
        $response = $this->withSession(['active_role' => 'Studio'])->post('/employees', [
            'name' => 'Maya Indah',
            'job_position' => 'Beauty Therapist',
            'department' => 'Studio Bandung',
            'skills' => ['Facial'],
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('employees', ['name' => 'Maya Indah']);
    }

    public function test_s17_create_treatment_action(): void
    {
        $response = $this->withSession(['active_role' => 'Studio'])->post('/studio/treatments', [
            'customer_id' => 'C001',
            'employee_id' => 'E001',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('beauty_treatments', ['customer_id' => 'C001']);
    }
}
