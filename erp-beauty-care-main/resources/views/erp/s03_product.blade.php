@extends('layouts.odoo')

@section('title', 'S-03 Form Product dan Variant SKU')
@section('breadcrumb', 'Inventory > Products > ' . ($selectedProduct->name ?? 'Product'))

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Products & Variant SKUs</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Views: <strong>Form</strong> &bull; List</span>
        </div>
        <form method="GET" action="{{ route('s03.product') }}">
            <select name="sku" onchange="this.form.submit()" class="text-xs border rounded p-1.5 bg-gray-50 focus:outline-none">
                @foreach($products as $p)
                    <option value="{{ $p->variant_sku }}" {{ $selectedProduct->variant_sku === $p->variant_sku ? 'selected' : '' }}>
                        {{ $p->variant_sku }} — {{ $p->name }} ({{ $p->color }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Product Form (Matching S-03 Wireframe) -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <div class="border-b pb-4 mb-4">
            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-amber-100 text-amber-800">Variant Master</span>
            <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $selectedProduct->variant_sku }} — {{ $selectedProduct->name }}</h2>
            <div class="text-xs text-gray-500">Unit: {{ $selectedProduct->unit }} &bull; Default Vendor: {{ $selectedProduct->default_vendor_id }}</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Variant SKU</label>
                <div class="p-2 bg-gray-50 rounded border text-gray-800 font-mono font-bold">{{ $selectedProduct->variant_sku }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Size</label>
                <div class="p-2 bg-gray-50 rounded border text-gray-800">{{ $selectedProduct->size }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Color / Varian</label>
                <div class="p-2 bg-gray-50 rounded border text-gray-800 font-semibold">{{ $selectedProduct->color }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sales Price (ex tax)</label>
                <div class="p-2 bg-gray-50 rounded border text-gray-800 font-bold text-emerald-700">Rp{{ number_format($selectedProduct->sales_price, 0, ',', '.') }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Standard Cost</label>
                <div class="p-2 bg-gray-50 rounded border text-gray-800 font-bold text-blue-700">Rp{{ number_format($selectedProduct->cost, 0, ',', '.') }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Unit of Measure</label>
                <div class="p-2 bg-gray-50 rounded border text-gray-800">{{ $selectedProduct->unit }}</div>
            </div>
        </div>

        <!-- All 15 Products Table -->
        <div class="mt-8">
            <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Master Products (15 Variant SKUs)</h3>
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2.5">Variant SKU</th>
                            <th class="p-2.5">Product Name</th>
                            <th class="p-2.5">Size</th>
                            <th class="p-2.5">Color</th>
                            <th class="p-2.5">Unit</th>
                            <th class="p-2.5 text-right">Sales Price</th>
                            <th class="p-2.5 text-right">Standard Cost</th>
                            <th class="p-2.5">Default Vendor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($products as $p)
                            <tr class="hover:bg-amber-50/40 {{ $p->variant_sku === $selectedProduct->variant_sku ? 'bg-amber-50 font-medium' : '' }}">
                                <td class="p-2.5 font-mono text-purple-700">
                                    <a href="{{ route('s03.product', ['sku' => $p->variant_sku]) }}">{{ $p->variant_sku }}</a>
                                </td>
                                <td class="p-2.5">{{ $p->name }}</td>
                                <td class="p-2.5">{{ $p->size }}</td>
                                <td class="p-2.5">{{ $p->color }}</td>
                                <td class="p-2.5">{{ $p->unit }}</td>
                                <td class="p-2.5 text-right font-semibold">Rp{{ number_format($p->sales_price, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right text-gray-600">Rp{{ number_format($p->cost, 0, ',', '.') }}</td>
                                <td class="p-2.5 font-mono">{{ $p->default_vendor_id }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create New Product / Variant SKU Form (Interactive Addition) -->
        <div class="mt-8 border-t pt-6">
            <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                <i class="fa-solid fa-box-archive text-amber-600 mr-2"></i>
                Tambah Produk / Varian SKU Baru
            </h3>
            <form action="{{ route('s03.create') }}" method="POST" class="bg-gray-50 p-4 rounded-lg border text-xs grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                @csrf
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Variant SKU (Unik)</label>
                    <input type="text" name="variant_sku" required placeholder="Contoh: BC-LIP-003" class="w-full border rounded p-1.5 bg-white font-mono focus:outline-none">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Nama Produk</label>
                    <input type="text" name="name" required placeholder="Lip Velvet" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Ukuran (Size)</label>
                    <input type="text" name="size" required placeholder="10 g / 100 ml" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Warna / Varian</label>
                    <input type="text" name="color" required placeholder="Ruby / Bening" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Satuan Unit</label>
                    <select name="unit" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                        <option value="botol">botol</option>
                        <option value="tube">tube</option>
                        <option value="jar">jar</option>
                        <option value="sachet">sachet</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Harga Jual (Sales Price)</label>
                    <input type="number" name="sales_price" required value="75000" min="0" step="1000" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Harga Pokok (Standard Cost)</label>
                    <input type="number" name="cost" required value="35000" min="0" step="1000" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Vendor Default</label>
                    <div class="flex space-x-2">
                        <select name="default_vendor_id" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}">{{ $v->id }} - {{ $v->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-1.5 px-3 rounded shadow transition whitespace-nowrap">
                            <i class="fa-solid fa-plus mr-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
