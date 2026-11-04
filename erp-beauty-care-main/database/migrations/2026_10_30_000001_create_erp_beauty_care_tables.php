<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Users modification (role)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('Sales'); // Sales, Warehouse, Finance, Studio, Purchasing, Owner
            }
        });

        // 2. Contacts (Customers & Vendors)
        Schema::create('contacts', function (Blueprint $table) {
            $table->string('id', 20)->primary(); // C001, V01, etc.
            $table->string('name');
            $table->string('type')->default('Perorangan'); // Perorangan, Perusahaan, Vendor
            $table->string('contact_type')->default('customer'); // customer, vendor
            $table->string('city')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->string('payment_terms')->default('Net 14');
            $table->decimal('opening_receivable', 15, 2)->default(0);
            $table->decimal('opening_payable', 15, 2)->default(0);
            $table->timestamps();
        });

        // 3. Products
        Schema::create('products', function (Blueprint $table) {
            $table->string('variant_sku', 30)->primary(); // BC-CLN-100, etc.
            $table->string('name');
            $table->string('size');
            $table->string('color');
            $table->string('unit'); // botol, sachet, jar, tube
            $table->decimal('sales_price', 15, 2)->default(0);
            $table->decimal('cost', 15, 2)->default(0); // standard cost
            $table->string('default_vendor_id', 20)->nullable();
            $table->timestamps();
        });

        // 4. Vendor Pricelist
        Schema::create('vendor_pricelists', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_id', 20);
            $table->string('variant_sku', 30);
            $table->integer('min_qty')->default(1);
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->integer('lead_time_days')->default(7);
            $table->timestamps();
        });

        // 5. Warehouses / Locations
        Schema::create('warehouses_locations', function (Blueprint $table) {
            $table->string('location_code', 20)->primary(); // WH-JKT, ST-BDG
            $table->string('location_name');
            $table->string('city');
            $table->string('type'); // Warehouse, Store / studio
            $table->timestamps();
        });

        // 6. Stock Quants (Location x SKU on_hand & reserved)
        Schema::create('stock_quants', function (Blueprint $table) {
            $table->id();
            $table->string('location_code', 20);
            $table->string('variant_sku', 30);
            $table->integer('on_hand')->default(0);
            $table->integer('reserved')->default(0);
            $table->unique(['location_code', 'variant_sku']);
            $table->timestamps();
        });

        // 7. Stock Moves
        Schema::create('stock_moves', function (Blueprint $table) {
            $table->id();
            $table->string('move_number', 40)->unique();
            $table->string('reference_doc_type', 40); // Delivery Order, Receipt, Internal Transfer, Treatment, Return
            $table->string('reference_doc_id', 40);
            $table->string('variant_sku', 30);
            $table->string('from_location', 20)->nullable();
            $table->string('to_location', 20)->nullable();
            $table->integer('qty');
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->string('status', 20)->default('done'); // draft, done, cancelled
            $table->boolean('is_quarantine')->default(false);
            $table->timestamps();
        });

        // 8. Chart of Accounts
        Schema::create('accounts', function (Blueprint $table) {
            $table->string('code', 10)->primary(); // 1010, 1100, etc.
            $table->string('name');
            $table->string('type'); // Asset, Liability, Equity, Income, Expense
            $table->timestamps();
        });

        // 9. Journal Entries & Items
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->string('entry_number', 40)->primary(); // JE/2026/00007
            $table->date('date');
            $table->string('reference', 50)->nullable();
            $table->string('source_doc_type', 40)->nullable();
            $table->string('source_doc_id', 40)->nullable();
            $table->string('status', 20)->default('Posted'); // Draft, Posted, Cancelled
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->string('posted_by', 50)->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('journal_items', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 40);
            $table->string('account_code', 10);
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 10. Employees
        Schema::create('employees', function (Blueprint $table) {
            $table->string('id', 20)->primary(); // E001..E004
            $table->string('name');
            $table->string('job_position');
            $table->string('department');
            $table->text('skills'); // serialized or JSON
            $table->string('status')->default('Aktif'); // Aktif, Nonaktif
            $table->timestamps();
        });

        // 11. Beauty Services & Treatments
        Schema::create('beauty_services', function (Blueprint $table) {
            $table->string('service_id', 30)->primary(); // SV-FAC-01
            $table->string('name');
            $table->string('duration');
            $table->decimal('sales_price', 15, 2)->default(0);
            $table->string('consumed_variant_sku', 30);
            $table->integer('qty_per_session')->default(1);
            $table->string('required_skill');
            $table->timestamps();
        });

        Schema::create('beauty_treatments', function (Blueprint $table) {
            $table->string('treatment_number', 40)->primary(); // TRT/2026/00007
            $table->string('service_id', 30);
            $table->string('customer_id', 20);
            $table->string('employee_id', 20);
            $table->string('location_code', 20)->default('ST-BDG');
            $table->string('consumed_sku', 30);
            $table->integer('consumed_qty')->default(1);
            $table->decimal('billed_total', 15, 2)->default(0);
            $table->string('status', 20)->default('Draft'); // Draft, Confirmed, Done
            $table->string('invoice_id', 40)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 12. CRM Leads
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->string('id', 30)->primary(); // LEAD-0001
            $table->string('customer_id', 20)->nullable();
            $table->string('name');
            $table->string('salesperson')->default('Kevin Mahendra');
            $table->string('stage', 30)->default('New'); // New, Qualified, Proposition, Won
            $table->string('status', 30)->default('Lead'); // Lead, Opportunity
            $table->string('quotation_id', 40)->nullable();
            $table->timestamps();
        });

        // 13. Sales Orders & Lines
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->string('order_number', 40)->primary(); // S00006, S00007
            $table->string('customer_id', 20);
            $table->string('warehouse_code', 20)->default('WH-JKT');
            $table->string('payment_terms', 30)->default('Net 14');
            $table->string('status', 20)->default('Draft'); // Draft, Confirmed, Done, Cancelled
            $table->decimal('untaxed_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('created_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_order_lines', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 40);
            $table->string('variant_sku', 30);
            $table->integer('qty');
            $table->integer('delivered_qty')->default(0);
            $table->integer('invoiced_qty')->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(11); // 11% effective
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });

        // 14. Delivery Orders & Lines
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->string('do_number', 40)->primary(); // WH/OUT/00007
            $table->string('sales_order_number', 40);
            $table->string('customer_id', 20);
            $table->string('source_location', 20)->default('WH-JKT');
            $table->string('status', 20)->default('Draft'); // Draft, Confirmed, Done, Cancelled
            $table->boolean('is_backorder')->default(false);
            $table->string('origin_do_number', 40)->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_order_lines', function (Blueprint $table) {
            $table->id();
            $table->string('do_number', 40);
            $table->string('variant_sku', 30);
            $table->integer('demand_qty');
            $table->integer('reserved_qty')->default(0);
            $table->integer('done_qty')->default(0);
            $table->integer('backorder_qty')->default(0);
            $table->timestamps();
        });

        // 15. Customer Invoices & Lines
        Schema::create('customer_invoices', function (Blueprint $table) {
            $table->string('invoice_number', 40)->primary(); // INV/2026/00007
            $table->string('customer_id', 20);
            $table->string('source_doc_type', 40); // Sales Order, Treatment, Delivery Order
            $table->string('source_doc_id', 40);
            $table->string('sales_order_number', 40)->nullable();
            $table->string('delivery_order_number', 40)->nullable();
            $table->string('status', 20)->default('Draft'); // Draft, Confirmed, Posted, Paid, Cancelled
            $table->decimal('untaxed_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('open_amount', 15, 2)->default(0);
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 40);
            $table->string('item_type', 20)->default('product'); // product, service
            $table->string('item_code', 30); // variant_sku or service_id
            $table->string('description')->nullable();
            $table->integer('qty');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });

        // 16. Purchase Orders & Lines
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->string('po_number', 40)->primary(); // RFQ00007, PO00006, PO00007
            $table->string('vendor_id', 20);
            $table->string('payment_terms', 30)->default('Net 30');
            $table->string('status', 20)->default('Draft'); // Draft, Confirmed, Done, Cancelled
            $table->decimal('untaxed_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->boolean('is_locked')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 40);
            $table->string('variant_sku', 30);
            $table->integer('qty_ordered');
            $table->integer('qty_received')->default(0);
            $table->integer('qty_billed')->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });

        // 17. Purchase Receipts & Lines
        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->string('receipt_number', 40)->primary(); // WH/IN/00007
            $table->string('po_number', 40);
            $table->string('vendor_id', 20);
            $table->string('destination_location', 20)->default('WH-JKT');
            $table->string('status', 20)->default('Draft'); // Draft, Confirmed, Done, Cancelled
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_receipt_lines', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 40);
            $table->string('variant_sku', 30);
            $table->integer('ordered_qty');
            $table->integer('accepted_qty')->default(0);
            $table->integer('rejected_qty')->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->timestamps();
        });

        // 18. Vendor Bills & Lines
        Schema::create('vendor_bills', function (Blueprint $table) {
            $table->string('bill_number', 40)->primary(); // BILL/2026/00007
            $table->string('vendor_id', 20);
            $table->string('po_number', 40);
            $table->string('receipt_number', 40)->nullable();
            $table->string('status', 20)->default('Draft'); // Draft, Confirmed, Posted, Paid, Cancelled
            $table->decimal('untaxed_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('open_amount', 15, 2)->default(0);
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_bill_lines', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number', 40);
            $table->string('variant_sku', 30);
            $table->integer('po_qty')->default(0);
            $table->integer('accepted_qty')->default(0);
            $table->integer('already_billed_qty')->default(0);
            $table->integer('bill_qty')->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });

        // 19. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->string('payment_number', 40)->primary(); // PAY/2026/00001
            $table->string('payment_type', 30); // customer_payment, vendor_payment
            $table->string('partner_id', 20);
            $table->string('partner_type', 20); // customer, vendor
            $table->string('invoice_id', 40)->nullable();
            $table->string('bill_id', 40)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('payment_date');
            $table->string('journal_account_code', 10)->default('1010'); // 1010 Bank
            $table->string('status', 20)->default('Posted'); // Draft, Posted, Cancelled
            $table->string('journal_entry_number', 40)->nullable();
            $table->timestamps();
        });

        // 20. Internal Stock Transfers
        Schema::create('internal_transfers', function (Blueprint $table) {
            $table->string('transfer_number', 40)->primary(); // WH/INT/00007
            $table->string('from_location', 20)->default('WH-JKT');
            $table->string('to_location', 20)->default('ST-BDG');
            $table->string('variant_sku', 30);
            $table->integer('qty');
            $table->string('status', 20)->default('Draft'); // Draft, Confirmed, Done, Cancelled
            $table->timestamp('transferred_at')->nullable();
            $table->timestamps();
        });

        // 21. Returns & Lines
        Schema::create('returns', function (Blueprint $table) {
            $table->string('return_number', 40)->primary(); // WH/RET/00007
            $table->string('origin_invoice_number', 40);
            $table->string('customer_id', 20);
            $table->string('inspection_status', 40)->default('Sealed / Accepted'); // Sealed / Accepted, Damaged / Rejected
            $table->string('status', 20)->default('Draft'); // Draft, Confirmed, Done, Cancelled
            $table->decimal('credit_note_total', 15, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('return_lines', function (Blueprint $table) {
            $table->id();
            $table->string('return_number', 40);
            $table->string('variant_sku', 30);
            $table->integer('delivered_qty');
            $table->integer('already_returned_qty')->default(0);
            $table->integer('return_qty');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });

        // 22. Audit Logs & Notes
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor');
            $table->string('role');
            $table->string('action'); // Confirm, Validate, Post, Complete, Cancel, Access Denied, Blocked
            $table->string('document_type');
            $table->string('document_id');
            $table->text('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('log_notes', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            $table->string('document_type');
            $table->string('document_id');
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_notes');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('return_lines');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('internal_transfers');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('vendor_bill_lines');
        Schema::dropIfExists('vendor_bills');
        Schema::dropIfExists('purchase_receipt_lines');
        Schema::dropIfExists('purchase_receipts');
        Schema::dropIfExists('purchase_order_lines');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('customer_invoice_lines');
        Schema::dropIfExists('customer_invoices');
        Schema::dropIfExists('delivery_order_lines');
        Schema::dropIfExists('delivery_orders');
        Schema::dropIfExists('sales_order_lines');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('crm_leads');
        Schema::dropIfExists('beauty_treatments');
        Schema::dropIfExists('beauty_services');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('journal_items');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('stock_moves');
        Schema::dropIfExists('stock_quants');
        Schema::dropIfExists('warehouses_locations');
        Schema::dropIfExists('vendor_pricelists');
        Schema::dropIfExists('products');
        Schema::dropIfExists('contacts');
        
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
