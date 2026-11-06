@extends('layouts.odoo')

@section('title', 'S-10 RFQ dan Purchase Order')
@section('breadcrumb', 'Purchase > Orders > ' . ($selectedOrder->po_number ?? 'PO'))

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Purchase Order</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Locked Upon Confirmation</span>
        </div>
        <form method="GET" action="{{ route('s10.purchase') }}">
            <select name="po" onchange="this.form.submit()" class="text-xs border rounded p-1.5 bg-gray-50 focus:outline-none">
                @foreach($orders as $o)
                    <option value="{{ $o->po_number }}" {{ $selectedOrder && $selectedOrder->po_number === $o->po_number ? 'selected' : '' }}>
                        {{ $o->po_number }} — {{ $o->vendor->name ?? $o->vendor_id }} ({{ $o->status }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Main PO Form -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <!-- Status Bar -->
        <div class="flex flex-wrap items-center justify-between border-b pb-4 mb-4 gap-3">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Status:</span>
                <div class="inline-flex rounded-md shadow-sm">
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedOrder->status ?? '') === 'Draft' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-l">
                        RFQ
                    </span>
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedOrder->status ?? '') === 'Confirmed' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                        Purchase Order
                    </span>
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedOrder->status ?? '') === 'Done' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-r">
                        Done
                    </span>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                @php
                    $canOpenRec = in_array($user->role, ['Admin', 'Owner', 'Warehouse', 'Inventory']) || \App\Services\RoleAccessService::hasGrantForFeature($user->role, 'Purchase Receipt');
                    $canOpenBill = in_array($user->role, ['Admin', 'Owner', 'Finance', 'Purchasing']) || \App\Services\RoleAccessService::hasGrantForFeature($user->role, 'Vendor Bill');
                @endphp
                @if($selectedOrder && $selectedOrder->receipts->count() > 0)
                    @if($canOpenRec)
                        <a href="{{ route('s11.receipt', ['rec' => $selectedOrder->receipts->first()->receipt_number]) }}" class="px-3 py-1.5 rounded bg-blue-50 border border-blue-300 text-blue-800 text-xs font-bold hover:bg-blue-100">
                            <i class="fa-solid fa-box-open mr-1"></i> Receipt ({{ $selectedOrder->receipts->count() }})
                        </a>
                    @else
                        <span class="px-3 py-1.5 rounded bg-blue-50 border border-blue-200 text-blue-800 text-xs font-bold">
                            <i class="fa-solid fa-box-open mr-1"></i> Receipt: {{ $selectedOrder->receipts->first()->receipt_number }}
                        </span>
                    @endif
                @endif
                @if($selectedOrder && $selectedOrder->bills->count() > 0)
                    @if($canOpenBill)
                        <a href="{{ route('s12.bill', ['bill' => $selectedOrder->bills->first()->bill_number]) }}" class="px-3 py-1.5 rounded bg-teal-50 border border-teal-300 text-teal-800 text-xs font-bold hover:bg-teal-100">
                            <i class="fa-solid fa-file-invoice mr-1"></i> Vendor Bill ({{ $selectedOrder->bills->count() }})
                        </a>
                    @else
                        <span class="px-3 py-1.5 rounded bg-teal-50 border border-teal-200 text-teal-800 text-xs font-bold">
                            <i class="fa-solid fa-file-invoice mr-1"></i> Bill: {{ $selectedOrder->bills->first()->bill_number }}
                        </span>
                    @endif
                @endif
            </div>
        </div>

        @if($selectedOrder)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm mb-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">PO Number</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-bold font-mono">{{ $selectedOrder->po_number }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Vendor</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-semibold">{{ $selectedOrder->vendor->name ?? $selectedOrder->vendor_id }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Payment Terms</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800">{{ $selectedOrder->payment_terms }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Lock Status</label>
                    <div class="p-2 bg-gray-50 rounded border text-purple-700 font-bold flex items-center">
                        <i class="fa-solid fa-lock mr-1.5 text-purple-500"></i> Locked after Confirmed
                    </div>
                </div>
            </div>

            <!-- Lines Table (S-10) -->
            <div class="border rounded-lg overflow-hidden mb-6">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2.5">Variant SKU</th>
                            <th class="p-2.5">Product Name</th>
                            <th class="p-2.5 text-center">Qty Ordered</th>
                            <th class="p-2.5 text-right">Unit Cost</th>
                            <th class="p-2.5 text-right">Untaxed</th>
                            <th class="p-2.5 text-right">Tax (11%)</th>
                            <th class="p-2.5 text-right font-bold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($selectedOrder->lines as $line)
                            <tr>
                                <td class="p-2.5 font-mono font-bold text-purple-700">{{ $line->variant_sku }}</td>
                                <td class="p-2.5">{{ $line->product->name ?? '' }}</td>
                                <td class="p-2.5 text-center font-bold">{{ $line->qty_ordered }}</td>
                                <td class="p-2.5 text-right">Rp{{ number_format($line->unit_cost, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right">Rp{{ number_format($line->unit_cost * $line->qty_ordered, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right text-gray-600">Rp{{ number_format($line->tax_amount, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right font-bold text-gray-800">Rp{{ number_format($line->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold border-t">
                        <tr>
                            <td colspan="6" class="p-2.5 text-right text-gray-600">Untaxed:</td>
                            <td class="p-2.5 text-right">Rp{{ number_format($selectedOrder->untaxed_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="p-2.5 text-right text-gray-600">Tax 11%:</td>
                            <td class="p-2.5 text-right text-gray-600">Rp{{ number_format($selectedOrder->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="text-sm bg-purple-50 text-purple-900 font-bold">
                            <td colspan="6" class="p-2.5 text-right">Total:</td>
                            <td class="p-2.5 text-right">Rp{{ number_format($selectedOrder->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <!-- Create New PO Form (Purchasing / Superuser / Granted) -->
        @if($canCreatePo ?? true)
            <div class="mt-8 border-t pt-6">
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                    <i class="fa-solid fa-cart-shopping text-blue-600 mr-2"></i>
                    Create New Purchase Order
                </h3>
                <form action="{{ route('s10.create') }}" method="POST" class="bg-gray-50 p-4 rounded-lg border text-xs grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    @csrf
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Vendor</label>
                        <select name="vendor_id" class="w-full border rounded p-1.5 bg-white">
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}" {{ $v->id === 'V01' ? 'selected' : '' }}>
                                    {{ $v->id }} — {{ $v->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Product SKU</label>
                        <select name="variant_sku" class="w-full border rounded p-1.5 bg-white">
                            @foreach($products as $p)
                                <option value="{{ $p->variant_sku }}" {{ $p->variant_sku === 'BC-TON-100' ? 'selected' : '' }}>
                                    {{ $p->variant_sku }} — {{ $p->name }} (Cost: Rp{{ number_format($p->cost, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Qty Ordered</label>
                        <input type="number" name="qty_ordered" value="5" min="1" class="w-full border rounded p-1.5 bg-white">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-3 rounded shadow">
                            <i class="fa-solid fa-check mr-1"></i> Confirm PO
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="mt-6 border-t pt-4 text-xs text-gray-500 italic">
                <i class="fa-solid fa-eye mr-1 text-gray-400"></i> Mode Tinjau (Read-Only): Konfirmasi Purchase Order baru dilakukan oleh divisi Purchasing.
            </div>
        @endif
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
