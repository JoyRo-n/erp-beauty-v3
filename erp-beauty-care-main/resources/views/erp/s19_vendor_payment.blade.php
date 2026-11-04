@extends('layouts.odoo')

@section('title', 'S-19 Vendor Payment and AP Settlement')
@section('breadcrumb', 'Accounting > Payments > Vendor Payment')

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Vendor Payment & AP Settlement</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Only for Posted Bills, Max Open AP (SR-23 / DL-19)</span>
        </div>
        <div class="text-xs text-gray-500">
            Journal: <strong>1010 Bank</strong>
        </div>
    </div>

    <!-- Payment Register Form (Matching S-19 Wireframe) -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        @if($selectedBill)
            <form action="{{ route('s19.register') }}" method="POST" class="max-w-2xl mx-auto space-y-4">
                @csrf
                <div class="border-b pb-3 mb-3">
                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-blue-100 text-blue-800">Vendor Payment Form</span>
                    <h3 class="text-lg font-bold text-gray-800 mt-1">Disburse Payment for {{ $selectedBill->bill_number }}</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Vendor Bill</label>
                        <input type="text" name="bill_number" value="{{ $selectedBill->bill_number }}" readonly class="w-full border rounded p-2 bg-gray-50 font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Payee (Vendor)</label>
                        <input type="text" value="{{ $selectedBill->vendor->name ?? $selectedBill->vendor_id }}" readonly class="w-full border rounded p-2 bg-gray-50 font-semibold">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Outstanding AP (Max Allowed)</label>
                        <input type="text" value="Rp{{ number_format($selectedBill->open_amount, 0, ',', '.') }}" readonly class="w-full border rounded p-2 bg-purple-50 text-purple-900 font-bold">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Payment Method / Bank</label>
                        <input type="text" value="1010 — Bank" readonly class="w-full border rounded p-2 bg-gray-50">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-medium text-gray-700 mb-1">Pay Amount (Rp)</label>
                        <input type="number" name="amount" value="{{ $selectedBill->open_amount }}" min="1" max="{{ $selectedBill->open_amount }}" step="1" class="w-full border-2 border-purple-400 rounded p-2 text-sm font-bold text-purple-900 focus:outline-none">
                        <p class="text-gray-400 text-xs mt-1">Entering more than Rp{{ number_format($selectedBill->open_amount, 0, ',', '.') }} will be rejected by DL-19.</p>
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <a href="{{ route('s12.bill', ['bill' => $selectedBill->bill_number]) }}" class="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 rounded bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow">
                        <i class="fa-solid fa-money-bill-transfer mr-1"></i> Confirm Vendor Payment
                    </button>
                </div>
            </form>
        @else
            <div class="text-center py-8 text-gray-400 italic">No unpaid posted vendor bills open.</div>
        @endif

        <!-- Recent Vendor Payments Table (S-19) -->
        <div class="mt-10 border-t pt-6">
            <h3 class="text-xs font-semibold uppercase text-gray-500 mb-3">Vendor Payment History (AP Settlement)</h3>
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2.5">Payment #</th>
                            <th class="p-2.5">Date</th>
                            <th class="p-2.5">Vendor Bill #</th>
                            <th class="p-2.5">Payee</th>
                            <th class="p-2.5 text-right font-bold">Amount Paid</th>
                            <th class="p-2.5">Journal Entry</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($payments as $p)
                            <tr>
                                <td class="p-2.5 font-mono font-bold text-purple-700">{{ $p->payment_number }}</td>
                                <td class="p-2.5 text-gray-500">{{ $p->payment_date }}</td>
                                <td class="p-2.5 font-mono">{{ $p->bill_id }}</td>
                                <td class="p-2.5">{{ $p->partner_id }}</td>
                                <td class="p-2.5 text-right font-bold text-rose-700">Rp{{ number_format($p->amount, 0, ',', '.') }}</td>
                                <td class="p-2.5 font-mono text-gray-500">{{ $p->journal_entry_number }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-3 text-center text-gray-400 italic">No vendor payments recorded yet.</td></tr>
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
