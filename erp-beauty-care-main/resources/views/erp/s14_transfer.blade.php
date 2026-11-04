@extends('layouts.odoo')

@section('title', 'S-14 Internal Stock Transfer')
@section('breadcrumb', 'Inventory > Internal Transfers')

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Internal Stock Transfer</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Valuation Remains Constant (SR-14 / DL-11)</span>
        </div>
    </div>

    <!-- Create Transfer Form (Matching S-14 Wireframe) -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <div class="border-b pb-3 mb-4">
            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-amber-100 text-amber-800">Inter-Warehouse Movement</span>
            <h2 class="text-lg font-bold text-gray-800 mt-1">Transfer Stock between WH-JKT & ST-BDG</h2>
        </div>

        <form action="{{ route('s14.execute') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 text-xs">
            @csrf
            <div>
                <label class="block font-medium text-gray-600 mb-1">From Location</label>
                <select name="from_location" class="w-full border rounded p-2 bg-white">
                    <option value="WH-JKT" selected>WH-JKT (Gudang Pusat)</option>
                    <option value="ST-BDG">ST-BDG (Glow Studio)</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-gray-600 mb-1">To Location</label>
                <select name="to_location" class="w-full border rounded p-2 bg-white">
                    <option value="ST-BDG" selected>ST-BDG (Glow Studio)</option>
                    <option value="WH-JKT">WH-JKT (Gudang Pusat)</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-gray-600 mb-1">Variant SKU</label>
                <select name="variant_sku" class="w-full border rounded p-2 bg-white">
                    @foreach($products as $p)
                        <option value="{{ $p->variant_sku }}" {{ $p->variant_sku === 'BC-SER-030' ? 'selected' : '' }}>
                            {{ $p->variant_sku }} — {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-medium text-gray-600 mb-1">Quantity</label>
                <input type="number" name="qty" value="3" min="1" class="w-full border rounded p-2 bg-white font-bold">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-3 rounded shadow">
                    <i class="fa-solid fa-arrows-left-right mr-1"></i> Execute Transfer
                </button>
            </div>
        </form>

        <!-- Transfer History Table (S-14) -->
        <div class="mt-8 border-t pt-6">
            <h3 class="text-xs font-semibold uppercase text-gray-500 mb-3">Recent Stock Transfers</h3>
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2.5">Transfer #</th>
                            <th class="p-2.5">From Location</th>
                            <th class="p-2.5">To Location</th>
                            <th class="p-2.5">Variant SKU</th>
                            <th class="p-2.5 text-center font-bold">Qty</th>
                            <th class="p-2.5">Timestamp</th>
                            <th class="p-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($transfers as $t)
                            <tr>
                                <td class="p-2.5 font-mono font-bold text-purple-700">{{ $t->transfer_number }}</td>
                                <td class="p-2.5 font-medium">{{ $t->from_location }}</td>
                                <td class="p-2.5 font-medium">{{ $t->to_location }}</td>
                                <td class="p-2.5 font-mono">{{ $t->variant_sku }}</td>
                                <td class="p-2.5 text-center font-bold">{{ $t->qty }}</td>
                                <td class="p-2.5 text-gray-500">{{ $t->transferred_at ?? $t->created_at }}</td>
                                <td class="p-2.5"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold">{{ $t->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-3 text-center text-gray-400 italic">No internal stock transfers recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
