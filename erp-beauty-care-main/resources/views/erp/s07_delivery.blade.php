@extends('layouts.odoo')

@section('title', 'S-07 Delivery Order dan Backorder')
@section('breadcrumb', 'Inventory > Delivery Orders > ' . ($selectedDo->do_number ?? 'DO'))

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Delivery Order & Backorder</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Partial Delivery & Stock Issue</span>
        </div>
        <form method="GET" action="{{ route('s07.delivery') }}">
            <select name="do" onchange="this.form.submit()" class="text-xs border rounded p-1.5 bg-gray-50 focus:outline-none">
                @foreach($orders as $d)
                    <option value="{{ $d->do_number }}" {{ $selectedDo && $selectedDo->do_number === $d->do_number ? 'selected' : '' }}>
                        {{ $d->do_number }} — SO: {{ $d->sales_order_number }} ({{ $d->status }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Main Delivery Order Form -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <!-- Status Bar -->
        <div class="flex flex-wrap items-center justify-between border-b pb-4 mb-4 gap-3">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Status:</span>
                <div class="inline-flex rounded-md shadow-sm">
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedDo->status ?? '') === 'Draft' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-l">
                        Draft
                    </span>
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedDo->status ?? '') === 'Confirmed' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                        Confirmed
                    </span>
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedDo->status ?? '') === 'Done' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-r">
                        Done
                    </span>
                </div>
            </div>

            @if($selectedDo && $selectedDo->status !== 'Done')
                <div>
                    <button type="submit" form="form-validate-do" class="px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow">
                        <i class="fa-solid fa-check-double mr-1"></i> Validate Delivery (F-09)
                    </button>
                </div>
            @endif
        </div>

        @if($selectedDo)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm mb-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Delivery Order</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-bold font-mono">{{ $selectedDo->do_number }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Source Sales Order</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-mono">{{ $selectedDo->sales_order_number }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Customer</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800">{{ $selectedDo->customer->name ?? $selectedDo->customer_id }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Source Location</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-semibold">{{ $selectedDo->source_location }}</div>
                </div>
            </div>

            <!-- Lines Table (S-07) -->
            <form id="form-validate-do" action="{{ route('s07.validate', $selectedDo->do_number) }}" method="POST">
                @csrf
                <div class="border rounded-lg overflow-hidden mb-6">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th class="p-2.5">Variant SKU</th>
                                <th class="p-2.5">Product Name</th>
                                <th class="p-2.5 text-center">Demand Qty</th>
                                <th class="p-2.5 text-center">Reserved Qty</th>
                                <th class="p-2.5 text-center">Done Qty (Delivered)</th>
                                <th class="p-2.5 text-center">Backorder Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($selectedDo->lines as $line)
                                <tr>
                                    <td class="p-2.5 font-mono font-bold text-purple-700">{{ $line->variant_sku }}</td>
                                    <td class="p-2.5">{{ $line->product->name ?? '' }}</td>
                                    <td class="p-2.5 text-center font-bold">{{ $line->demand_qty }}</td>
                                    <td class="p-2.5 text-center font-semibold text-amber-600">{{ $line->reserved_qty }}</td>
                                    <td class="p-2.5 text-center">
                                        @if($selectedDo->status === 'Done')
                                            <span class="font-bold text-emerald-700 text-sm">{{ $line->done_qty }}</span>
                                        @else
                                            <input type="number" name="done_qty[{{ $line->variant_sku }}]" value="{{ $line->reserved_qty }}" min="1" max="{{ $line->reserved_qty }}" class="w-20 text-center border rounded p-1 bg-white font-bold text-purple-700 focus:outline-none">
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-center font-bold text-rose-600">{{ $line->backorder_qty }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        @endif
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
