<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\HundredDatasetSeeder;

class SeedHundredCommand extends Command
{
    protected $signature = 'erp:seed-100';
    protected $description = 'Populate database with 100+ comprehensive encrypted ERP records across Sales, Purchasing, Inventory, Studio, and Accounting';

    public function handle(): int
    {
        $this->info('Seeding 100+ ERP records across all divisions (AES-256 Encrypted)...');
        $seeder = new HundredDatasetSeeder();
        $seeder->run();
        $this->info('Successfully populated 100+ records across Customers, Products, CRM, Sales, Deliveries, Invoices, Purchasing, Receipts, Bills, Payments, Studio, and General Ledger!');

        $tables = [
            'contacts', 'products', 'crm_leads', 'sales_orders', 'delivery_orders',
            'customer_invoices', 'purchase_orders', 'purchase_receipts', 'vendor_bills',
            'payments', 'internal_transfers', 'beauty_treatments', 'returns',
            'employees', 'journal_entries', 'journal_items', 'stock_moves'
        ];
        foreach ($tables as $tbl) {
            $this->line(str_pad($tbl, 22) . ': ' . \Illuminate\Support\Facades\DB::table($tbl)->count());
        }

        return Command::SUCCESS;
    }
}
