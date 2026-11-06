@extends('layouts.odoo')

@section('title', 'S-09 Register Payment')
@section('breadcrumb', 'Accounting > Payments > Register Customer Payment')

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Register Customer Payment</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Partial Payment & No Overpayment</span>
        </div>
        <div class="text-xs text-gray-500">
            Journal: <strong>1010 Bank</strong>
        </div>
    </div>

    <!-- Payment Register Form (Matching S-09 Wireframe) -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        @if($selectedInvoice)
            <form action="{{ route('s09.register') }}" method="POST" class="max-w-2xl mx-auto space-y-4">
                @csrf
                <div class="border-b pb-3 mb-3">
                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Payment Form</span>
                    <h3 class="text-lg font-bold text-gray-800 mt-1">Receive Payment for {{ $selectedInvoice->invoice_number }}</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Invoice</label>
                        <input type="text" name="invoice_number" value="{{ $selectedInvoice->invoice_number }}" readonly class="w-full border rounded p-2 bg-gray-50 font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Customer</label>
                        <input type="text" value="{{ $selectedInvoice->customer->name ?? $selectedInvoice->customer_id }}" readonly class="w-full border rounded p-2 bg-gray-50 font-semibold">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Open Amount (Max Allowed)</label>
                        <input type="text" value="Rp{{ number_format($selectedInvoice->open_amount, 0, ',', '.') }}" readonly class="w-full border rounded p-2 bg-purple-50 text-purple-900 font-bold">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Bank Account</label>
                        <input type="text" value="1010 — Bank" readonly class="w-full border rounded p-2 bg-gray-50">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-medium text-gray-700 mb-1">Payment Amount (Rp)</label>
                        <input type="number" name="amount" value="{{ $selectedInvoice->open_amount }}" min="1" max="{{ $selectedInvoice->open_amount }}" step="1" class="w-full border-2 border-purple-400 rounded p-2 text-sm font-bold text-purple-900 focus:outline-none">
                        <p class="text-gray-400 text-xs mt-1">Entering more than Rp{{ number_format($selectedInvoice->open_amount, 0, ',', '.') }} will be rejected automatically.</p>
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <a href="{{ route('s08.invoice', ['inv' => $selectedInvoice->invoice_number]) }}" class="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 rounded bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow">
                        <i class="fa-solid fa-check mr-1"></i> Confirm Payment
                    </button>
                </div>
            </form>
        @else
            <div class="text-center py-8 text-gray-400 italic">No unpaid posted invoices currently open.</div>
        @endif

        <!-- Recent Customer Payments Table -->
        <div class="mt-10 border-t pt-6">
            <h3 class="text-xs font-semibold uppercase text-gray-500 mb-3">Customer Payment History</h3>
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2.5">Payment #</th>
                            <th class="p-2.5">Date</th>
                            <th class="p-2.5">Invoice #</th>
                            <th class="p-2.5">Customer</th>
                            <th class="p-2.5 text-right font-bold">Amount Paid</th>
                            <th class="p-2.5">Journal Entry</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($payments as $p)
                            <tr>
                                <td class="p-2.5 font-mono font-bold text-purple-700">{{ $p->payment_number }}</td>
                                <td class="p-2.5 text-gray-500">{{ $p->payment_date }}</td>
                                <td class="p-2.5 font-mono">{{ $p->invoice_id }}</td>
                                <td class="p-2.5">{{ $p->partner_id }}</td>
                                <td class="p-2.5 text-right font-bold text-emerald-700">Rp{{ number_format($p->amount, 0, ',', '.') }}</td>
                                <td class="p-2.5 font-mono text-gray-500">{{ $p->journal_entry_number }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-3 text-center text-gray-400 italic">No payments recorded yet.</td></tr>
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
