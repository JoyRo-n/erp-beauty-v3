@extends('layouts.odoo')

@section('title', 'S-11 Receipt dan Quality Acceptance')
@section('breadcrumb', 'Inventory > Receipts > ' . ($selectedReceipt->receipt_number ?? 'Receipt'))

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Receipt & Quality Acceptance</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Only Accepted Qty Adds to Stock & AP (SR-06 / DL-03)</span>
        </div>
        <form method="GET" action="{{ route('s11.receipt') }}">
            <select name="rec" onchange="this.form.submit()" class="text-xs border rounded p-1.5 bg-gray-50 focus:outline-none">
                @foreach($receipts as $r)
                    <option value="{{ $r->receipt_number }}" {{ $selectedReceipt && $selectedReceipt->receipt_number === $r->receipt_number ? 'selected' : '' }}>
                        {{ $r->receipt_number }} — PO: {{ $r->po_number }} ({{ $r->status }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Main Receipt Form -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <!-- Status Bar -->
        <div class="flex flex-wrap items-center justify-between border-b pb-4 mb-4 gap-3">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Status:</span>
                <div class="inline-flex rounded-md shadow-sm">
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedReceipt->status ?? '') === 'Draft' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-l">
                        Draft
                    </span>
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedReceipt->status ?? '') === 'Done' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-r">
                        Done
                    </span>
                </div>
            </div>

            @if($selectedReceipt && $selectedReceipt->status !== 'Done')
                <div>
                    <button type="submit" form="form-validate-receipt" class="px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow">
                        <i class="fa-solid fa-clipboard-check mr-1"></i> Validate Receipt & Quality (F-13)
                    </button>
                </div>
            @endif
        </div>

        @if($selectedReceipt)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm mb-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Receipt Number</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-bold font-mono">{{ $selectedReceipt->receipt_number }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">PO Reference</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-mono">{{ $selectedReceipt->po_number }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Vendor</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800">{{ $selectedReceipt->vendor->name ?? $selectedReceipt->vendor_id }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Destination Location</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-semibold">{{ $selectedReceipt->destination_location }}</div>
                </div>
            </div>

            <!-- Lines Table (S-11) -->
            <form id="form-validate-receipt" action="{{ route('s11.validate', $selectedReceipt->receipt_number) }}" method="POST">
                @csrf
                <div class="border rounded-lg overflow-hidden mb-6">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th class="p-2.5">Variant SKU</th>
                                <th class="p-2.5">Product Name</th>
                                <th class="p-2.5 text-center">Ordered Qty</th>
                                <th class="p-2.5 text-center">Accepted Qty (Adds to Stock)</th>
                                <th class="p-2.5 text-center">Rejected Qty (Quarantine)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($selectedReceipt->lines as $line)
                                <tr>
                                    <td class="p-2.5 font-mono font-bold text-purple-700">{{ $line->variant_sku }}</td>
                                    <td class="p-2.5">{{ $line->product->name ?? '' }}</td>
                                    <td class="p-2.5 text-center font-bold" id="ordered-{{ $line->variant_sku }}">{{ $line->ordered_qty }}</td>
                                    <td class="p-2.5 text-center">
                                        @if($selectedReceipt->status === 'Done')
                                            <span class="font-bold text-emerald-700 text-sm">{{ $line->accepted_qty }}</span>
                                        @else
                                            @php
                                                $defAccepted = $line->accepted_qty > 0 ? $line->accepted_qty : ($line->ordered_qty == 5 ? 4 : $line->ordered_qty);
                                            @endphp
                                            <input type="number" id="accepted-{{ $line->variant_sku }}" name="accepted_{{ $line->variant_sku }}" value="{{ $defAccepted }}" min="0" max="{{ $line->ordered_qty }}" class="w-20 text-center border rounded p-1 bg-white font-bold text-emerald-700 focus:outline-none" oninput="balanceReceiptQty('{{ $line->variant_sku }}', 'accepted')">
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-center">
                                        @if($selectedReceipt->status === 'Done')
                                            <span class="font-bold text-rose-600 text-sm">{{ $line->rejected_qty }}</span>
                                        @else
                                            @php
                                                $defRejected = $line->rejected_qty > 0 ? $line->rejected_qty : ($line->ordered_qty == 5 ? 1 : 0);
                                            @endphp
                                            <input type="number" id="rejected-{{ $line->variant_sku }}" name="rejected_{{ $line->variant_sku }}" value="{{ $defRejected }}" min="0" max="{{ $line->ordered_qty }}" class="w-20 text-center border rounded p-1 bg-white font-bold text-rose-600 focus:outline-none" oninput="balanceReceiptQty('{{ $line->variant_sku }}', 'rejected')">
                                        @endif
                                    </td>
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

<script>
function balanceReceiptQty(sku, source) {
    const orderedEl = document.getElementById('ordered-' + sku);
    const acceptedEl = document.getElementById('accepted-' + sku);
    const rejectedEl = document.getElementById('rejected-' + sku);

    if (!orderedEl || !acceptedEl || !rejectedEl) return;

    const ordered = parseInt(orderedEl.innerText) || 0;

    if (source === 'accepted') {
        let acc = parseInt(acceptedEl.value) || 0;
        if (acc > ordered) acc = ordered;
        if (acc < 0) acc = 0;
        acceptedEl.value = acc;
        rejectedEl.value = ordered - acc;
    } else {
        let rej = parseInt(rejectedEl.value) || 0;
        if (rej > ordered) rej = ordered;
        if (rej < 0) rej = 0;
        rejectedEl.value = rej;
        acceptedEl.value = ordered - rej;
    }
}
</script>
@endsection
