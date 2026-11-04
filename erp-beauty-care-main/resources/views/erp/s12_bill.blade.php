@extends('layouts.odoo')

@section('title', 'S-12 Vendor Bill dan Three-Way Match')
@section('breadcrumb', 'Accounting > Vendor Bills > ' . ($selectedBill->bill_number ?? 'Bill'))

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Vendor Bill & Three-Way Match</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Bill Qty Capped by Accepted Qty (SR-05 / SR-06 / DL-03)</span>
        </div>
        <form method="GET" action="{{ route('s12.bill') }}">
            <select name="bill" onchange="this.form.submit()" class="text-xs border rounded p-1.5 bg-gray-50 focus:outline-none">
                @foreach($bills as $b)
                    <option value="{{ $b->bill_number }}" {{ $selectedBill && $selectedBill->bill_number === $b->bill_number ? 'selected' : '' }}>
                        {{ $b->bill_number }} — {{ $b->vendor->name ?? $b->vendor_id }} ({{ $b->status }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Main Bill Form -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <!-- Status Bar -->
        <div class="flex flex-wrap items-center justify-between border-b pb-4 mb-4 gap-3">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Status:</span>
                <div class="inline-flex rounded-md shadow-sm">
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedBill->status ?? '') === 'Draft' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-l">
                        Draft
                    </span>
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedBill->status ?? '') === 'Posted' ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                        Posted
                    </span>
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedBill->status ?? '') === 'Paid' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-r">
                        Paid
                    </span>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                @if($selectedBill && $selectedBill->status === 'Draft')
                    <form action="{{ route('s12.post', $selectedBill->bill_number) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow">
                            <i class="fa-solid fa-stamp mr-1"></i> Post Vendor Bill (Finance)
                        </button>
                    </form>
                @elseif($selectedBill && $selectedBill->status === 'Posted' && $selectedBill->open_amount > 0)
                    <a href="{{ route('s19.vendor_payment', ['bill' => $selectedBill->bill_number]) }}" class="px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow">
                        <i class="fa-solid fa-money-bill-transfer mr-1"></i> Pay Bill (S-19)
                    </a>
                @endif
            </div>
        </div>

        @if($selectedBill)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm mb-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Bill Number</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-bold font-mono">{{ $selectedBill->bill_number }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Vendor</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800">{{ $selectedBill->vendor->name ?? $selectedBill->vendor_id }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">PO Reference</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-mono">{{ $selectedBill->po_number }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Open AP</label>
                    <div class="p-2 bg-purple-50 rounded border border-purple-200 text-purple-900 font-bold">
                        Rp{{ number_format($selectedBill->open_amount, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Lines Table (S-12) -->
            <div class="border rounded-lg overflow-hidden mb-6">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2.5">Variant SKU</th>
                            <th class="p-2.5 text-center">PO Qty</th>
                            <th class="p-2.5 text-center">Accepted Qty</th>
                            <th class="p-2.5 text-center">Already Billed</th>
                            <th class="p-2.5 text-center font-bold">Bill Qty</th>
                            <th class="p-2.5 text-right">Unit Cost</th>
                            <th class="p-2.5 text-right">Tax (11%)</th>
                            <th class="p-2.5 text-right font-bold">Total Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($selectedBill->lines as $line)
                            <tr>
                                <td class="p-2.5 font-mono font-bold text-purple-700">{{ $line->variant_sku }}</td>
                                <td class="p-2.5 text-center">{{ $line->po_qty }}</td>
                                <td class="p-2.5 text-center font-bold text-emerald-700">{{ $line->accepted_qty }}</td>
                                <td class="p-2.5 text-center">{{ $line->already_billed_qty }}</td>
                                <td class="p-2.5 text-center font-bold text-purple-800">{{ $line->bill_qty }}</td>
                                <td class="p-2.5 text-right">Rp{{ number_format($line->unit_cost, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right text-gray-600">Rp{{ number_format($line->tax_amount, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right font-bold text-gray-800">Rp{{ number_format($line->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold border-t">
                        <tr>
                            <td colspan="7" class="p-2.5 text-right text-gray-600">Untaxed (Stock Interim Clearing):</td>
                            <td class="p-2.5 text-right">Rp{{ number_format($selectedBill->untaxed_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="7" class="p-2.5 text-right text-gray-600">PPN Masukan (Input VAT 11%):</td>
                            <td class="p-2.5 text-right text-gray-600">Rp{{ number_format($selectedBill->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="text-sm bg-purple-50 text-purple-900 font-bold">
                            <td colspan="7" class="p-2.5 text-right">Total Accounts Payable (AP):</td>
                            <td class="p-2.5 text-right">Rp{{ number_format($selectedBill->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-400 italic">No vendor bills available. Receive goods from purchase orders first.</div>
        @endif
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
