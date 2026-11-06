@extends('layouts.odoo')

@section('title', 'S-18 Operational Dashboard dan Audit')
@section('breadcrumb', 'Accounting > Reporting > Operational Dashboard & Audit')

@section('content')
<div class="space-y-6">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-4 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div>
            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-purple-100 text-purple-800">Executive KPI Dashboard</span>
            <h1 class="text-xl font-bold text-gray-800 mt-1">Operational Dashboard & Drill-down</h1>
            <p class="text-xs text-gray-500">Scenario-Only Performance Metrics (Excludes Opening Balances)</p>
        </div>
        <div class="flex items-center space-x-2">
            <!-- 1-Click Presentation Demo Runner -->
            <form action="{{ route('demo.run') }}" method="POST" onsubmit="return confirm('Eksekusi alur otomatis end-to-end (O2C + P2P + Studio) untuk demonstrasi presentasi? Data baseline akan disiapkan terlebih dahulu.');">
                @csrf
                <button type="submit" class="bg-gradient-to-r from-purple-700 to-indigo-700 hover:from-purple-800 hover:to-indigo-800 text-white text-xs font-bold px-3 py-2 rounded-lg shadow flex items-center space-x-1.5 transition">
                    <i class="fa-solid fa-play-circle text-sm"></i>
                    <span>Jalankan Demo Alur Lengkap (1-Klik)</span>
                </button>
            </form>

            <span class="text-xs px-2.5 py-1.5 rounded bg-emerald-50 text-emerald-800 font-semibold border border-emerald-200">
                <i class="fa-solid fa-lock text-xs mr-1"></i> Role: {{ session('active_role', 'Sales') }}
            </span>
        </div>
    </div>

    <!-- 5 KPI Summary Cards (Matching S-18 Wireframe) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- 1. Scenario Revenue -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow transition">
            <div class="text-xs text-gray-500 font-medium">Scenario Revenue (ex tax)</div>
            <div class="text-2xl font-bold text-gray-800 mt-1">Rp{{ number_format($kpis['revenue'], 0, ',', '.') }}</div>
            <div class="text-xs text-purple-600 mt-2 font-medium flex items-center">
                <i class="fa-solid fa-arrow-down text-xs mr-1"></i> Drill-down to Invoices
            </div>
        </div>

        <!-- 2. Scenario COGS -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow transition">
            <div class="text-xs text-gray-500 font-medium">Scenario COGS</div>
            <div class="text-2xl font-bold text-rose-600 mt-1">Rp{{ number_format($kpis['cogs'], 0, ',', '.') }}</div>
            <div class="text-xs text-gray-500 mt-2 flex items-center">
                <span>Account 5000 & 5010</span>
            </div>
        </div>

        <!-- 3. Gross Contribution -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow transition">
            <div class="text-xs text-gray-500 font-medium">Gross Contribution</div>
            <div class="text-2xl font-bold text-emerald-600 mt-1">Rp{{ number_format($kpis['contribution'], 0, ',', '.') }}</div>
            <div class="text-xs text-gray-500 mt-2">Revenue - COGS</div>
        </div>

        <!-- 4. New Unpaid AR -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow transition">
            <div class="text-xs text-gray-500 font-medium">New Unpaid AR</div>
            <div class="text-2xl font-bold text-purple-700 mt-1">Rp{{ number_format($kpis['new_ar'], 0, ',', '.') }}</div>
            <div class="text-xs text-gray-500 mt-2">Open Invoices</div>
        </div>

        <!-- 5. Stock Valuation -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow transition">
            <div class="text-xs text-gray-500 font-medium">Total Stock Value</div>
            <div class="text-2xl font-bold text-blue-700 mt-1">Rp{{ number_format($kpis['stock_value'], 0, ',', '.') }}</div>
            <div class="text-xs text-gray-500 mt-2">WH-JKT + ST-BDG</div>
        </div>
    </div>

    <!-- Drill-Down Sections: Each figure opens source documents -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Drill-down: Posted Customer Invoices -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
            <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center justify-between">
                <span><i class="fa-solid fa-file-invoice-dollar text-purple-600 mr-2"></i> Revenue Source Documents</span>
                <span class="text-xs font-normal text-gray-500">Customer Invoices</span>
            </h3>
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2">Invoice #</th>
                            <th class="p-2">Customer</th>
                            <th class="p-2 text-right">Untaxed (Rev)</th>
                            <th class="p-2 text-right">PPN (11%)</th>
                            <th class="p-2 text-right">Total</th>
                            <th class="p-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($revenueInvoices as $inv)
                            <tr>
                                <td class="p-2 font-mono font-bold text-purple-700">
                                    <a href="{{ route('s08.invoice', ['inv' => $inv->invoice_number]) }}">{{ $inv->invoice_number }}</a>
                                </td>
                                <td class="p-2">{{ $inv->customer->name ?? $inv->customer_id }}</td>
                                <td class="p-2 text-right font-bold text-emerald-700">Rp{{ number_format($inv->untaxed_amount, 0, ',', '.') }}</td>
                                <td class="p-2 text-right text-gray-600">Rp{{ number_format($inv->tax_amount, 0, ',', '.') }}</td>
                                <td class="p-2 text-right font-semibold">Rp{{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                                <td class="p-2"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold">{{ $inv->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-3 text-center text-gray-400 italic">No scenario revenue invoices yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- COGS Drill-down: Delivery & Treatment Stock Moves -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
            <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center justify-between">
                <span><i class="fa-solid fa-boxes-packing text-rose-600 mr-2"></i> COGS Source Movements</span>
                <span class="text-xs font-normal text-gray-500">Outbound Delivery / Treatment</span>
            </h3>
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2">Ref Doc #</th>
                            <th class="p-2">Type</th>
                            <th class="p-2">Variant SKU</th>
                            <th class="p-2 text-center">Qty</th>
                            <th class="p-2 text-right font-bold">COGS Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($cogsMoves as $move)
                            <tr>
                                <td class="p-2 font-mono font-bold text-purple-700">{{ $move->reference_doc_id }}</td>
                                <td class="p-2">{{ $move->reference_doc_type }}</td>
                                <td class="p-2 font-mono">{{ $move->variant_sku }}</td>
                                <td class="p-2 text-center font-bold">{{ $move->qty }}</td>
                                <td class="p-2 text-right font-bold text-rose-600">Rp{{ number_format($move->total_cost, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-3 text-center text-gray-400 italic">No COGS movements recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Full System Audit Trail -->
    @include('partials.odoo_chatter', ['auditLogs' => $auditLogs])
</div>
@endsection
