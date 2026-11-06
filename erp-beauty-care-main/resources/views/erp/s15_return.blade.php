@extends('layouts.odoo')

@section('title', 'S-15 Return dan Credit Note')
@section('breadcrumb', 'Sales > Customer Returns & Credit Notes')

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Customer Return & Credit Note</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Reverse Stock, COGS & AR</span>
        </div>
        <form method="GET" action="{{ route('s15.return') }}">
            <select name="ret" onchange="this.form.submit()" class="text-xs border rounded p-1.5 bg-gray-50 focus:outline-none">
                @foreach($returns as $r)
                    <option value="{{ $r->return_number }}" {{ $selectedReturn && $selectedReturn->return_number === $r->return_number ? 'selected' : '' }}>
                        {{ $r->return_number }} — Inv: {{ $r->origin_invoice_number }} ({{ $r->inspection_status }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Main Return Form (Matching S-15 Wireframe) -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        @if($selectedReturn)
            <div class="border-b pb-4 mb-4 flex justify-between items-start">
                <div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-purple-100 text-purple-800">Return Document</span>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1 font-mono">{{ $selectedReturn->return_number }}</h2>
                    <div class="text-xs text-gray-500">Origin Invoice: <strong>{{ $selectedReturn->origin_invoice_number }}</strong> &bull; Customer: {{ $selectedReturn->customer_id }}</div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Credit Note Issued</div>
                    <div class="text-xl font-bold text-emerald-700 font-mono">Rp{{ number_format($selectedReturn->credit_note_total, 0, ',', '.') }}</div>
                    <span class="text-xs px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold">{{ $selectedReturn->inspection_status }}</span>
                </div>
            </div>

            <!-- Lines Table (S-15) -->
            <div class="border rounded-lg overflow-hidden mb-6">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2.5">Variant SKU</th>
                            <th class="p-2.5 text-center">Delivered Qty</th>
                            <th class="p-2.5 text-center">Already Returned</th>
                            <th class="p-2.5 text-center font-bold">Return Qty</th>
                            <th class="p-2.5 text-right">Unit Price</th>
                            <th class="p-2.5 text-right">Credit Base</th>
                            <th class="p-2.5 text-right">Tax (11%)</th>
                            <th class="p-2.5 text-right font-bold">Credit Note Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($selectedReturn->lines as $line)
                            <tr>
                                <td class="p-2.5 font-mono font-bold text-purple-700">{{ $line->variant_sku }}</td>
                                <td class="p-2.5 text-center">{{ $line->delivered_qty }}</td>
                                <td class="p-2.5 text-center">{{ $line->already_returned_qty }}</td>
                                <td class="p-2.5 text-center font-bold text-purple-900">{{ $line->return_qty }}</td>
                                <td class="p-2.5 text-right">Rp{{ number_format($line->unit_price, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right">Rp{{ number_format($line->unit_price * $line->return_qty, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right text-gray-600">Rp{{ number_format($line->tax_amount, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right font-bold text-gray-800">Rp{{ number_format($line->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-400 italic">No customer returns processed yet.</div>
        @endif

        <!-- Process New Return Form (Warehouse / Finance / Superuser) -->
        @if($canProcessReturn ?? false)
            <div class="mt-8 border-t pt-6">
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                    <i class="fa-solid fa-arrow-rotate-left text-purple-600 mr-2"></i>
                    Process Customer Return & Credit Note
                </h3>
                <form action="{{ route('s15.process') }}" method="POST" class="bg-gray-50 p-4 rounded-lg border text-xs grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                    @csrf
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Origin Invoice</label>
                        <select name="origin_invoice_number" class="w-full border rounded p-1.5 bg-white font-mono">
                            @forelse($invoices as $inv)
                                <option value="{{ $inv->invoice_number }}" {{ $inv->invoice_number === 'INV/2026/00007' ? 'selected' : '' }}>
                                    {{ $inv->invoice_number }} ({{ $inv->customer->name ?? $inv->customer_id }})
                                </option>
                            @empty
                                <option value="">-- Belum ada faktur terbit --</option>
                            @endforelse
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Variant SKU</label>
                        <input type="text" name="variant_sku" value="BC-CLN-100" class="w-full border rounded p-1.5 bg-white font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Return Qty (Max delivered)</label>
                        <input type="number" name="return_qty" value="1" min="1" class="w-full border rounded p-1.5 bg-white font-bold">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Quality Inspection</label>
                        <select name="inspection_status" class="w-full border rounded p-1.5 bg-white font-semibold">
                            <option value="Sealed / Accepted" selected>Sealed / Accepted (Restores Stock)</option>
                            <option value="Damaged / Rejected">Damaged / Rejected (No Stock Restore)</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-1.5 px-3 rounded shadow">
                            <i class="fa-solid fa-receipt mr-1"></i> Issue Credit Note
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="mt-6 border-t pt-4 text-xs text-gray-500 italic">
                <i class="fa-solid fa-eye mr-1 text-gray-400"></i> Mode Tinjau (Read-Only): Inspeksi fisik retur dan penerbitan Credit Note diproses oleh Warehouse &amp; Finance.
            </div>
        @endif
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
