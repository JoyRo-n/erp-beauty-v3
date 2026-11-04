<div class="mt-8 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden" x-data="{ tab: 'related' }">
    <div class="border-b border-gray-200 bg-gray-50 flex items-center justify-between px-4">
        <div class="flex space-x-6 text-xs font-semibold">
            <button onclick="switchChatterTab('related')" id="tab-btn-related" class="py-3 border-b-2 border-purple-600 text-purple-700 font-bold focus:outline-none">
                <i class="fa-solid fa-link mr-1"></i> Related Docs ({{ count($relatedDocs ?? []) }})
            </button>
            <button onclick="switchChatterTab('notes')" id="tab-btn-notes" class="py-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none">
                <i class="fa-regular fa-message mr-1"></i> Log Notes ({{ count($logNotes ?? []) }})
            </button>
            <button onclick="switchChatterTab('audit')" id="tab-btn-audit" class="py-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none">
                <i class="fa-solid fa-clock-rotate-left mr-1"></i> Audit / History ({{ count($auditLogs ?? []) }})
            </button>
        </div>
        <div class="text-xs text-gray-400">
            <span>SR-12 Traceability & Audit Engine</span>
        </div>
    </div>

    <!-- Related Docs Tab Pane -->
    <div id="pane-related" class="p-4 text-xs">
        @if(isset($relatedDocs) && count($relatedDocs) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($relatedDocs as $doc)
                    @php
                        $docUrl = '#';
                        if (!empty($doc->order_number)) {
                            $docUrl = route('s05.sales', ['order' => $doc->order_number]);
                        } elseif (!empty($doc->do_number)) {
                            $docUrl = route('s07.delivery', ['do' => $doc->do_number]);
                        } elseif (!empty($doc->invoice_number)) {
                            $docUrl = route('s08.invoice', ['inv' => $doc->invoice_number]);
                        } elseif (!empty($doc->po_number)) {
                            $docUrl = route('s10.purchase', ['po' => $doc->po_number]);
                        } elseif (!empty($doc->receipt_number)) {
                            $docUrl = route('s11.receipt', ['rec' => $doc->receipt_number]);
                        } elseif (!empty($doc->bill_number)) {
                            $docUrl = route('s12.bill', ['bill' => $doc->bill_number]);
                        } elseif (!empty($doc->treatment_number)) {
                            $docUrl = route('s17.treatment', ['trt' => $doc->treatment_number]);
                        }
                    @endphp
                    <a href="{{ $docUrl }}" class="p-3 bg-gray-50 border border-gray-200 rounded flex items-center justify-between hover:bg-purple-50 hover:border-purple-300 transition group block">
                        <div>
                            <div class="font-bold text-gray-800 group-hover:text-purple-700">
                                {{ $doc->order_number ?? $doc->do_number ?? $doc->invoice_number ?? $doc->po_number ?? $doc->receipt_number ?? $doc->bill_number ?? $doc->treatment_number ?? 'Doc' }}
                            </div>
                            <div class="text-gray-500">
                                Status: <span class="font-semibold text-purple-700">{{ $doc->status ?? 'Done' }}</span>
                            </div>
                        </div>
                        <span class="text-gray-400 group-hover:text-purple-600 transition"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-gray-400 italic py-2 text-center">No related documents linked to this record yet.</div>
        @endif
    </div>

    <!-- Log Notes Tab Pane -->
    <div id="pane-notes" class="p-4 text-xs hidden">
        @if(isset($logNotes) && count($logNotes) > 0)
            <div class="space-y-3">
                @foreach($logNotes as $note)
                    <div class="p-3 bg-purple-50/50 border-l-4 border-purple-500 rounded">
                        <div class="flex items-center justify-between text-gray-500 mb-1">
                            <span class="font-bold text-gray-700">{{ $note->author }}</span>
                            <span>{{ $note->created_at }}</span>
                        </div>
                        <div class="text-gray-800">{{ $note->message }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-gray-400 italic py-2 text-center">No log notes recorded for this document.</div>
        @endif
    </div>

    <!-- Audit / History Tab Pane -->
    <div id="pane-audit" class="p-4 text-xs hidden">
        @if(isset($auditLogs) && count($auditLogs) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 border-b">
                            <th class="p-2">Timestamp</th>
                            <th class="p-2">Actor</th>
                            <th class="p-2">Role</th>
                            <th class="p-2">Action</th>
                            <th class="p-2">Target</th>
                            <th class="p-2">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditLogs as $log)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-2 text-gray-500">{{ $log->created_at }}</td>
                                <td class="p-2 font-medium">{{ $log->actor }}</td>
                                <td class="p-2"><span class="px-1.5 py-0.5 rounded bg-gray-200 text-gray-700 font-semibold">{{ $log->role }}</span></td>
                                <td class="p-2">
                                    <span class="font-bold {{ str_contains($log->action, 'Blocked') || str_contains($log->action, 'Denied') ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="p-2">{{ $log->document_type }} #{{ $log->document_id }}</td>
                                <td class="p-2 text-gray-600">{{ $log->details }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-gray-400 italic py-2 text-center">No audit trail records found.</div>
        @endif
    </div>
</div>

<script>
    function switchChatterTab(tab) {
        document.getElementById('pane-related').classList.add('hidden');
        document.getElementById('pane-notes').classList.add('hidden');
        document.getElementById('pane-audit').classList.add('hidden');

        document.getElementById('tab-btn-related').className = "py-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none";
        document.getElementById('tab-btn-notes').className = "py-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none";
        document.getElementById('tab-btn-audit').className = "py-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none";

        document.getElementById('pane-' + tab).classList.remove('hidden');
        document.getElementById('tab-btn-' + tab).className = "py-3 border-b-2 border-purple-600 text-purple-700 font-bold focus:outline-none";
    }
</script>
