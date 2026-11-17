@extends('layouts.odoo')

@section('title', 'S-13 Journal Entries dan General Ledger')
@section('breadcrumb', 'Accounting > Journal Entries & General Ledger')

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex flex-wrap items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Journal Entries & General Ledger</span>
            <span class="text-gray-400">|</span>
            <a href="{{ route('s18.dashboard') }}" class="text-xs text-rose-700 hover:underline font-medium">Operational Dashboard</a>
            <span class="text-gray-300">&bull;</span>
            <a href="{{ route('s19.vendor_payment') }}" class="text-xs text-rose-700 hover:underline font-medium">Vendor Payments</a>
            <span class="text-gray-300">&bull;</span>
            <a href="{{ route('s09.payment') }}" class="text-xs text-rose-700 hover:underline font-medium">Customer Payments</a>
        </div>
        <form method="GET" action="{{ route('s13.ledger') }}">
            <select name="entry" onchange="this.form.submit()" class="text-xs border rounded p-1.5 bg-gray-50 focus:outline-none">
                @foreach($entries as $e)
                    <option value="{{ $e->entry_number }}" {{ $selectedEntry && $selectedEntry->entry_number === $e->entry_number ? 'selected' : '' }}>
                        {{ $e->entry_number }} — Ref: {{ $e->reference }} (Rp{{ number_format($e->total_debit, 0, ',', '.') }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Main Journal Entry Form (Matching S-13 Wireframe) -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        @if($selectedEntry)
            <div class="border-b pb-4 mb-4 flex justify-between items-start">
                <div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Posted Journal</span>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1 font-mono">{{ $selectedEntry->entry_number }}</h2>
                    <div class="text-xs text-gray-500">Date: {{ optional($selectedEntry->date)->format('Y-m-d') ?? substr((string) $selectedEntry->date, 0, 10) }} &bull; Reference: <strong>{{ $selectedEntry->reference }}</strong></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Debit / Credit Total</div>
                    <div class="text-lg font-bold text-rose-700 font-mono">Rp{{ number_format($selectedEntry->total_debit, 0, ',', '.') }}</div>
                    <div class="text-xs text-emerald-600 font-semibold"><i class="fa-solid fa-scale-balanced mr-1"></i> Balanced (Diff: 0)</div>
                </div>
            </div>

            <!-- Items Table (S-13) -->
            <div class="border rounded-lg overflow-hidden mb-6">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2.5">Account Code</th>
                            <th class="p-2.5">Account Name</th>
                            <th class="p-2.5">Description</th>
                            <th class="p-2.5 text-right font-bold">Debit (Rp)</th>
                            <th class="p-2.5 text-right font-bold">Credit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($selectedEntry->items as $item)
                            <tr>
                                <td class="p-2.5 font-mono font-bold text-rose-700">{{ $item->account_code }}</td>
                                <td class="p-2.5 font-medium {{ $item->credit > 0 ? 'pl-8 text-gray-700' : 'text-gray-900 font-semibold' }}">{{ $item->account->name ?? '' }}</td>
                                <td class="p-2.5 text-gray-600">{{ $item->description }}</td>
                                <td class="p-2.5 text-right font-mono {{ $item->debit > 0 ? 'font-bold text-gray-800' : 'text-gray-400' }}">
                                    {{ $item->debit > 0 ? 'Rp' . number_format($item->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-2.5 text-right font-mono {{ $item->credit > 0 ? 'font-bold text-gray-800' : 'text-gray-400' }}">
                                    {{ $item->credit > 0 ? 'Rp' . number_format($item->credit, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-[#FFF2F7] text-rose-950 font-bold border-t">
                        <tr>
                            <td colspan="3" class="p-2.5 text-right">Totals:</td>
                            <td class="p-2.5 text-right font-mono">Rp{{ number_format($selectedEntry->total_debit, 0, ',', '.') }}</td>
                            <td class="p-2.5 text-right font-mono">Rp{{ number_format($selectedEntry->total_credit, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <!-- General Ledger List (Each row has ONLY Debit OR Credit, never both) -->
        <div class="mt-8">
            <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">All Posted Journal Entries</h3>
            <div class="overflow-x-auto border rounded-lg max-h-[36rem] overflow-y-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600 sticky top-0 z-10">
                        <tr>
                            <th class="p-2.5">Entry #</th>
                            <th class="p-2.5">Date</th>
                            <th class="p-2.5">Reference</th>
                            <th class="p-2.5">Account &amp; Source Type</th>
                            <th class="p-2.5 text-right">Debit</th>
                            <th class="p-2.5 text-right">Credit</th>
                            <th class="p-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($ledgerLines as $line)
                            @php
                                $e = $line->entry;
                                $isSelectedRow = $selectedEntry && $e && $selectedEntry->entry_number === $e->entry_number;
                            @endphp
                            <tr class="hover:bg-[#FFF2F7]/70 {{ $isSelectedRow ? 'bg-[#FFF2F7]' : '' }}">
                                <td class="p-2.5 font-mono text-rose-700 font-semibold">
                                    <a href="{{ route('s13.ledger', ['entry' => $line->entry_number]) }}">{{ $line->entry_number }}</a>
                                </td>
                                <td class="p-2.5 text-gray-500 font-mono">{{ optional($e?->date)->format('Y-m-d') ?? substr((string) ($e?->date ?? ''), 0, 10) }}</td>
                                <td class="p-2.5 font-medium text-gray-800">{{ $e?->reference }}</td>
                                <td class="p-2.5 {{ $line->credit > 0 ? 'pl-7 text-gray-600' : 'font-semibold text-gray-900' }}">
                                    <span class="font-mono text-rose-700 mr-1">{{ $line->account_code }}</span>
                                    {{ $line->account->name ?? '' }}
                                    <span class="text-[10px] text-gray-400 ml-1">({{ $e?->source_doc_type }})</span>
                                </td>
                                <td class="p-2.5 text-right font-mono {{ $line->debit > 0 ? 'font-bold text-gray-900' : 'text-gray-300' }}">
                                    {{ $line->debit > 0 ? 'Rp' . number_format($line->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-2.5 text-right font-mono {{ $line->credit > 0 ? 'font-bold text-gray-900' : 'text-gray-300' }}">
                                    {{ $line->credit > 0 ? 'Rp' . number_format($line->credit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-2.5"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold">{{ $e?->status ?? 'Posted' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-[#FFF2F7] text-rose-950 font-bold border-t sticky bottom-0">
                        <tr>
                            <td colspan="4" class="p-2.5 text-right">Total Balance:</td>
                            <td class="p-2.5 text-right font-mono">Rp{{ number_format($ledgerLines->sum('debit'), 0, ',', '.') }}</td>
                            <td class="p-2.5 text-right font-mono">Rp{{ number_format($ledgerLines->sum('credit'), 0, ',', '.') }}</td>
                            <td class="p-2.5 text-emerald-700 font-semibold">Balanced</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
