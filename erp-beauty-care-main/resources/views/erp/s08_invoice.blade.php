@extends('layouts.odoo')

@section('title', 'S-08 Customer Invoice')
@section('breadcrumb', 'Invoicing > Customer Invoices > ' . ($selectedInvoice->invoice_number ?? 'Invoice'))

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Customer Invoice</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Invoicing Delivered Quantity (SR-02 / DL-01)</span>
        </div>
        <form method="GET" action="{{ route('s08.invoice') }}">
            <select name="inv" onchange="this.form.submit()" class="text-xs border rounded p-1.5 bg-gray-50 focus:outline-none">
                @foreach($invoices as $i)
                    <option value="{{ $i->invoice_number }}" {{ $selectedInvoice && $selectedInvoice->invoice_number === $i->invoice_number ? 'selected' : '' }}>
                        {{ $i->invoice_number }} — {{ $i->customer->name ?? $i->customer_id }} ({{ $i->status }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Main Invoice Form -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <!-- Status Bar -->
        <div class="flex flex-wrap items-center justify-between border-b pb-4 mb-4 gap-3">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Status:</span>
                <div class="inline-flex rounded-md shadow-sm">
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedInvoice->status ?? '') === 'Draft' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-l">
                        Draft
                    </span>
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedInvoice->status ?? '') === 'Posted' ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                        Posted
                    </span>
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedInvoice->status ?? '') === 'Paid' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-r">
                        Paid
                    </span>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                @if($selectedInvoice && $selectedInvoice->status === 'Draft')
                    <form action="{{ route('s08.post', $selectedInvoice->invoice_number) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow">
                            <i class="fa-solid fa-stamp mr-1"></i> Post Invoice (Finance)
                        </button>
                    </form>
                @elseif($selectedInvoice && $selectedInvoice->status === 'Posted' && $selectedInvoice->open_amount > 0)
                    <a href="{{ route('s09.payment', ['inv' => $selectedInvoice->invoice_number]) }}" class="px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow">
                        <i class="fa-solid fa-money-bill-wave mr-1"></i> Register Payment (S-09)
                    </a>
                @endif
            </div>
        </div>

        @if($selectedInvoice)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm mb-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Invoice Number</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-bold font-mono">{{ $selectedInvoice->invoice_number }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Customer</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800">{{ $selectedInvoice->customer->name ?? $selectedInvoice->customer_id }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Source Document</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-mono">{{ $selectedInvoice->source_doc_type }} #{{ $selectedInvoice->source_doc_id }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Open Balance (AR)</label>
                    <div class="p-2 bg-purple-50 rounded border border-purple-200 text-purple-900 font-bold">
                        Rp{{ number_format($selectedInvoice->open_amount, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Lines Table (S-08) -->
            <div class="border rounded-lg overflow-hidden mb-6">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2.5">Item / SKU</th>
                            <th class="p-2.5">Description</th>
                            <th class="p-2.5 text-center">Delivered Qty</th>
                            <th class="p-2.5 text-right">Unit Price</th>
                            <th class="p-2.5 text-right">Untaxed</th>
                            <th class="p-2.5 text-right">Tax (11%)</th>
                            <th class="p-2.5 text-right font-bold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($selectedInvoice->lines as $line)
                            <tr>
                                <td class="p-2.5 font-mono font-bold text-purple-700">{{ $line->item_code }}</td>
                                <td class="p-2.5">{{ $line->description }}</td>
                                <td class="p-2.5 text-center font-bold">{{ $line->qty }}</td>
                                <td class="p-2.5 text-right">Rp{{ number_format($line->unit_price, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right">Rp{{ number_format($line->unit_price * $line->qty, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right text-gray-600">Rp{{ number_format($line->tax_amount, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right font-bold text-gray-800">Rp{{ number_format($line->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold border-t">
                        <tr>
                            <td colspan="6" class="p-2.5 text-right text-gray-600">Untaxed Amount:</td>
                            <td class="p-2.5 text-right">Rp{{ number_format($selectedInvoice->untaxed_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="p-2.5 text-right text-gray-600">PPN (11% Effective):</td>
                            <td class="p-2.5 text-right text-gray-600">Rp{{ number_format($selectedInvoice->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="text-sm bg-purple-50 text-purple-900 font-bold">
                            <td colspan="6" class="p-2.5 text-right">Total Amount:</td>
                            <td class="p-2.5 text-right">Rp{{ number_format($selectedInvoice->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-400 italic">No customer invoices available. Deliver an order or complete a treatment first.</div>
        @endif
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
