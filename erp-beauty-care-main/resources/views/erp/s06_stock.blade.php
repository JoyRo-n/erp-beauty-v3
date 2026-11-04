@extends('layouts.odoo')

@section('title', 'S-06 Stock Overview dan Reservation')
@section('breadcrumb', 'Inventory > Stock Overview')

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Stock Overview & Reservation</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Available = On Hand - Reserved (SR-04)</span>
        </div>
        <div class="flex items-center space-x-4 text-xs">
            <span class="px-2.5 py-1 rounded bg-blue-50 border border-blue-200 text-blue-800">
                Total On Hand: <strong>{{ $totalOnHand }}</strong>
            </span>
            <span class="px-2.5 py-1 rounded bg-amber-50 border border-amber-200 text-amber-800">
                Total Reserved: <strong>{{ $totalReserved }}</strong>
            </span>
            <span class="px-2.5 py-1 rounded bg-emerald-50 border border-emerald-200 text-emerald-800">
                Total Available: <strong>{{ $totalAvailable }}</strong>
            </span>
        </div>
    </div>

    <!-- Stock Tables Split by Location (WH-JKT and ST-BDG) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- WH-JKT Location -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
            <div class="flex items-center justify-between border-b pb-3 mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    <h3 class="font-bold text-gray-800 text-sm">WH-JKT (Gudang Pusat Jakarta)</h3>
                </div>
                <span class="text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-600 font-medium">Warehouse</span>
            </div>
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2">Variant SKU</th>
                            <th class="p-2">Product Name</th>
                            <th class="p-2 text-center">On Hand</th>
                            <th class="p-2 text-center">Reserved</th>
                            <th class="p-2 text-center font-bold">Available</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($whJktStocks as $stock)
                            <tr class="hover:bg-blue-50/30 {{ $stock->variant_sku === 'BC-CLN-100' ? 'bg-amber-50/50' : '' }}">
                                <td class="p-2 font-mono font-bold text-purple-700">{{ $stock->variant_sku }}</td>
                                <td class="p-2">{{ $stock->product->name ?? '' }}</td>
                                <td class="p-2 text-center font-semibold">{{ $stock->on_hand }}</td>
                                <td class="p-2 text-center font-semibold text-amber-600">{{ $stock->reserved }}</td>
                                <td class="p-2 text-center font-bold text-emerald-700 bg-emerald-50/50">{{ $stock->available }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ST-BDG Location -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
            <div class="flex items-center justify-between border-b pb-3 mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-pink-500"></span>
                    <h3 class="font-bold text-gray-800 text-sm">ST-BDG (Glow Studio Bandung)</h3>
                </div>
                <span class="text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-600 font-medium">Store / studio</span>
            </div>
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2">Variant SKU</th>
                            <th class="p-2">Product Name</th>
                            <th class="p-2 text-center">On Hand</th>
                            <th class="p-2 text-center">Reserved</th>
                            <th class="p-2 text-center font-bold">Available</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($stBdgStocks as $stock)
                            <tr class="hover:bg-pink-50/30 {{ $stock->variant_sku === 'BC-MSK-001' ? 'bg-purple-50/50' : '' }}">
                                <td class="p-2 font-mono font-bold text-purple-700">{{ $stock->variant_sku }}</td>
                                <td class="p-2">{{ $stock->product->name ?? '' }}</td>
                                <td class="p-2 text-center font-semibold">{{ $stock->on_hand }}</td>
                                <td class="p-2 text-center font-semibold text-amber-600">{{ $stock->reserved }}</td>
                                <td class="p-2 text-center font-bold text-emerald-700 bg-emerald-50/50">{{ $stock->available }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
