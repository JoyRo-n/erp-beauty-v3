@extends('layouts.odoo')

@section('title', 'S-13 Journal Entries dan General Ledger')
@section('breadcrumb', 'Accounting > Journal Entries & General Ledger')

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Journal Entries & General Ledger</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Debit = Credit Balanced (SR-16 / DL-13)</span>
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
                    <div class="text-xs text-gray-500">Date: {{ $selectedEntry->date }} &bull; Reference: <strong>{{ $selectedEntry->reference }}</strong></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Debit / Credit Total</div>
                    <div class="text-lg font-bold text-purple-700 font-mono">Rp{{ number_format($selectedEntry->total_debit, 0, ',', '.') }}</div>
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
                                <td class="p-2.5 font-mono font-bold text-purple-700">{{ $item->account_code }}</td>
                                <td class="p-2.5 font-medium">{{ $item->account->name ?? '' }}</td>
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
                    <tfoot class="bg-purple-50 text-purple-900 font-bold border-t">
                        <tr>
                            <td colspan="3" class="p-2.5 text-right">Totals:</td>
                            <td class="p-2.5 text-right font-mono">Rp{{ number_format($selectedEntry->total_debit, 0, ',', '.') }}</td>
                            <td class="p-2.5 text-right font-mono">Rp{{ number_format($selectedEntry->total_credit, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <!-- General Ledger List -->
        <div class="mt-8">
            <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">All Posted Journal Entries</h3>
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2.5">Entry #</th>
                            <th class="p-2.5">Date</th>
                            <th class="p-2.5">Reference</th>
                            <th class="p-2.5">Source Type</th>
                            <th class="p-2.5 text-right">Debit</th>
                            <th class="p-2.5 text-right">Credit</th>
                            <th class="p-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($entries as $e)
                            <tr class="hover:bg-purple-50/40 {{ $selectedEntry && $selectedEntry->entry_number === $e->entry_number ? 'bg-purple-50 font-medium' : '' }}">
                                <td class="p-2.5 font-mono text-purple-700">
                                    <a href="{{ route('s13.ledger', ['entry' => $e->entry_number]) }}">{{ $e->entry_number }}</a>
                                </td>
                                <td class="p-2.5 text-gray-500">{{ $e->date }}</td>
                                <td class="p-2.5">{{ $e->reference }}</td>
                                <td class="p-2.5">{{ $e->source_doc_type }}</td>
                                <td class="p-2.5 text-right font-mono font-semibold">Rp{{ number_format($e->total_debit, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right font-mono font-semibold">Rp{{ number_format($e->total_credit, 0, ',', '.') }}</td>
                                <td class="p-2.5"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold">{{ $e->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
