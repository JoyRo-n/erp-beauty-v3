<?php

namespace App\Services;

use App\Models\JournalItem;
use App\Models\CustomerInvoice;
use App\Models\StockQuant;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get scenario-only KPI (excluding opening balances) (DL-17 / SR-20 / F-21 / T-35)
     */
    public static function getScenarioKpi(): array
    {
        // 1. Scenario Revenue (Account 4000 & 4010, excluding JE/2026/OPENING)
        $revenue = (float) JournalItem::whereIn('account_code', ['4000', '4010'])
            ->where('entry_number', '!=', 'JE/2026/OPENING')
            ->sum(DB::raw('credit - debit'));

        // 2. Scenario COGS (Account 5000 & 5010, excluding JE/2026/OPENING)
        $cogs = (float) JournalItem::whereIn('account_code', ['5000', '5010'])
            ->where('entry_number', '!=', 'JE/2026/OPENING')
            ->sum(DB::raw('debit - credit'));

        // 3. Gross Contribution = Revenue - COGS
        $contribution = $revenue - $cogs;

        // 4. New Unpaid AR (Customer Invoices created during scenarios, excluding opening AR)
        $newAr = (float) CustomerInvoice::whereIn('status', ['Posted'])
            ->sum('open_amount');

        // 5. Total Stock Value
        $stockValuation = (float) DB::table('stock_quants')
            ->join('products', 'stock_quants.variant_sku', '=', 'products.variant_sku')
            ->sum(DB::raw('stock_quants.on_hand * products.cost'));

        return [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'contribution' => $contribution,
            'new_ar' => $newAr,
            'stock_value' => $stockValuation,
        ];
    }
}
