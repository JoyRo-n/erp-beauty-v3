<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\StockQuant;
use App\Models\Product;
use App\Models\Contact;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Support\Facades\DB;

class BaselineVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $seeder = new \Database\Seeders\BaselineSeeder();
        $seeder->run();
    }

    public function test_baseline_control_numbers(): void
    {
        $totalOnHand = StockQuant::sum('on_hand');
        $this->assertEquals(396, $totalOnHand, 'Total on hand must be 396 units');

        $totalReserved = StockQuant::sum('reserved');
        $this->assertEquals(3, $totalReserved, 'Total reserved must be 3 units (from S00006)');

        $clnJkt = StockQuant::where('location_code', 'WH-JKT')
            ->where('variant_sku', 'BC-CLN-100')
            ->first();
        $this->assertEquals(15, $clnJkt->on_hand);
        $this->assertEquals(3, $clnJkt->reserved);
        $this->assertEquals(12, $clnJkt->available);

        $totalValuation = DB::table('stock_quants')
            ->join('products', 'stock_quants.variant_sku', '=', 'products.variant_sku')
            ->sum(DB::raw('stock_quants.on_hand * products.cost'));
        $this->assertEquals(22612500, (float) $totalValuation, 'Stock valuation must be Rp22.612.500');

        $bank = JournalItem::where('account_code', '1010')->sum(DB::raw('debit - credit'));
        $this->assertEquals(50000000, (float) $bank, 'Bank must be Rp50.000.000');

        $ar = JournalItem::where('account_code', '1100')->sum(DB::raw('debit - credit'));
        $this->assertEquals(4440000, (float) $ar, 'Opening AR must be Rp4.440.000');

        $ap = JournalItem::where('account_code', '2100')->sum(DB::raw('credit - debit'));
        $this->assertEquals(2220000, (float) $ap, 'Opening AP must be Rp2.220.000');

        $equity = JournalItem::where('account_code', '3100')->sum(DB::raw('credit - debit'));
        $this->assertEquals(74832500, (float) $equity, 'Opening Capital must be Rp74.832.500');
    }
}
