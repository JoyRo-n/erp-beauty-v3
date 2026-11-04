@extends('layouts.odoo')

@section('title', 'S-05 Quotation dan Sales Order')
@section('breadcrumb', 'Sales > Orders > ' . ($selectedOrder->order_number ?? 'Order'))

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Quotation & Sales Order</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Document Actions</span>
        </div>
        <div class="flex items-center space-x-3">
            <form method="GET" action="{{ route('s05.sales') }}">
                <select name="order" onchange="this.form.submit()" class="text-xs border rounded p-1.5 bg-gray-50 focus:outline-none">
                    @foreach($orders as $o)
                        <option value="{{ $o->order_number }}" {{ $selectedOrder && $selectedOrder->order_number === $o->order_number ? 'selected' : '' }}>
                            {{ $o->order_number }} — {{ $o->customer->name ?? $o->customer_id }} ({{ $o->status }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Main Order Form (Matching S-05 Wireframe) -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <!-- Odoo 17 Status Widget Bar (Draft -> Confirmed -> Done) -->
        <div class="flex flex-wrap items-center justify-between border-b pb-4 mb-4 gap-3">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Document Status:</span>
                <div class="inline-flex rounded-md shadow-sm">
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedOrder->status ?? '') === 'Draft' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-l">
                        Draft
                    </span>
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedOrder->status ?? '') === 'Confirmed' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                        Confirmed
                    </span>
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedOrder->status ?? '') === 'Done' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-r">
                        Done
                    </span>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                @if($selectedOrder && $selectedOrder->deliveryOrders->count() > 0)
                    <a href="{{ route('s07.delivery', ['do' => $selectedOrder->deliveryOrders->first()->do_number]) }}" class="px-3 py-1.5 rounded bg-amber-50 border border-amber-300 text-amber-800 text-xs font-bold hover:bg-amber-100">
                        <i class="fa-solid fa-truck-fast mr-1"></i> Delivery ({{ $selectedOrder->deliveryOrders->count() }})
                    </a>
                @endif
                @if($selectedOrder && $selectedOrder->invoices->count() > 0)
                    <a href="{{ route('s08.invoice', ['inv' => $selectedOrder->invoices->first()->invoice_number]) }}" class="px-3 py-1.5 rounded bg-teal-50 border border-teal-300 text-teal-800 text-xs font-bold hover:bg-teal-100">
                        <i class="fa-solid fa-file-invoice mr-1"></i> Invoice ({{ $selectedOrder->invoices->count() }})
                    </a>
                @endif
            </div>
        </div>

        @if($selectedOrder)
            <!-- Existing Order Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm mb-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Order Number</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-bold font-mono">{{ $selectedOrder->order_number }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Customer</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-semibold">{{ $selectedOrder->customer->id ?? '' }} — {{ $selectedOrder->customer->name ?? '' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Warehouse Location</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800">{{ $selectedOrder->warehouse_code }}</div>
                </div>
            </div>

            <!-- Lines Table (S-05) -->
            <div class="border rounded-lg overflow-hidden mb-6">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2.5">Variant SKU</th>
                            <th class="p-2.5">Product Name</th>
                            <th class="p-2.5 text-center">Qty Ordered</th>
                            <th class="p-2.5 text-center">Delivered</th>
                            <th class="p-2.5 text-right">Unit Price</th>
                            <th class="p-2.5 text-center">Discount %</th>
                            <th class="p-2.5 text-right">Tax (11%)</th>
                            <th class="p-2.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($selectedOrder->lines as $line)
                            <tr>
                                <td class="p-2.5 font-mono font-bold text-purple-700">{{ $line->variant_sku }}</td>
                                <td class="p-2.5">{{ $line->product->name ?? '' }}</td>
                                <td class="p-2.5 text-center font-bold">{{ $line->qty }}</td>
                                <td class="p-2.5 text-center font-bold text-amber-600">{{ $line->delivered_qty }}</td>
                                <td class="p-2.5 text-right">Rp{{ number_format($line->unit_price, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-center">{{ $line->discount_percent }}%</td>
                                <td class="p-2.5 text-right text-gray-600">Rp{{ number_format($line->tax_amount, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right font-bold text-gray-800">Rp{{ number_format($line->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold border-t">
                        <tr>
                            <td colspan="7" class="p-2.5 text-right text-gray-600">Untaxed Amount:</td>
                            <td class="p-2.5 text-right">Rp{{ number_format($selectedOrder->untaxed_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="7" class="p-2.5 text-right text-gray-600">PPN (11% Effective):</td>
                            <td class="p-2.5 text-right text-gray-600">Rp{{ number_format($selectedOrder->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="text-sm bg-purple-50 text-purple-900 font-bold">
                            <td colspan="7" class="p-2.5 text-right">Total Amount:</td>
                            <td class="p-2.5 text-right">Rp{{ number_format($selectedOrder->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <!-- Create New Sales Order Form (Interactive Testing) -->
        <div class="mt-8 border-t pt-6">
            <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                <i class="fa-solid fa-cart-plus text-purple-600 mr-2"></i>
                Create New Quotation / Sales Order (Testing DL-04 & DL-05)
            </h3>
            <form action="{{ route('s05.create') }}" method="POST" class="bg-gray-50 p-4 rounded-lg border text-xs grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                @csrf
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Customer</label>
                    <select name="customer_id" class="w-full border rounded p-1.5 bg-white">
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ $c->id === 'C001' ? 'selected' : '' }}>
                                {{ $c->id }} - {{ $c->name }} (Limit: Rp{{ number_format($c->credit_limit, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Product SKU</label>
                    <select name="variant_sku" class="w-full border rounded p-1.5 bg-white">
                        @foreach($products as $p)
                            <option value="{{ $p->variant_sku }}" {{ $p->variant_sku === 'BC-CLN-100' ? 'selected' : '' }}>
                                {{ $p->variant_sku }} - {{ $p->name }} (Rp{{ number_format($p->sales_price, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Quantity</label>
                    <input type="number" name="qty" value="2" min="1" class="w-full border rounded p-1.5 bg-white">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Discount % (Max 15%)</label>
                    <input type="number" name="discount_percent" value="0" min="0" max="100" class="w-full border rounded p-1.5 bg-white">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-1.5 px-3 rounded shadow">
                        <i class="fa-solid fa-check mr-1"></i> Confirm SO
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
