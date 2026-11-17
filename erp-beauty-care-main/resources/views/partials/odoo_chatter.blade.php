@php
    $chatterCurrentRole = session('active_role', auth()->user()->role ?? 'Sales');
    // Audit / History is visible ONLY to Master Admin ('Admin') or when Master Admin is inspecting a staff division
    $canViewAuditTrail = ($chatterCurrentRole === 'Admin') || !empty(session('origin_super_user_id'));
@endphp
<div class="mt-8 bg-white border border-[#F2C6D8] rounded-xl shadow-sm overflow-hidden" x-data="{ tab: 'related' }">
    <div class="border-b border-[#F2C6D8] bg-gradient-to-r from-[#FFF0F5] via-[#FADADD]/65 to-[#FFF7FA] flex items-center justify-between px-5">
        <div class="flex space-x-6 text-xs font-semibold">
            <button onclick="switchChatterTab('related')" id="tab-btn-related" class="py-3 border-b-2 border-[#D95386] text-rose-900 font-bold focus:outline-none">
                <i class="fa-solid fa-link mr-1 text-rose-500"></i> Related Docs ({{ count($relatedDocs ?? []) }})
            </button>
            <button onclick="switchChatterTab('notes')" id="tab-btn-notes" class="py-3 border-b-2 border-transparent text-rose-700/70 hover:text-rose-900 focus:outline-none">
                <i class="fa-regular fa-message mr-1"></i> Log Notes ({{ count($logNotes ?? []) }})
            </button>
            @if($canViewAuditTrail)
                <button onclick="switchChatterTab('audit')" id="tab-btn-audit" class="py-3 border-b-2 border-transparent text-rose-700/70 hover:text-rose-900 focus:outline-none">
                    <i class="fa-solid fa-clock-rotate-left mr-1"></i> Audit / History ({{ count($auditLogs ?? []) }})
                </button>
            @endif
        </div>
        <div class="text-xs text-rose-500 font-medium hidden sm:block">
            <i class="fa-solid fa-sparkles mr-1 text-rose-400"></i><span>{{ $canViewAuditTrail ? 'Traceability & Audit Trail (Master Admin)' : 'Document Traceability & Notes' }}</span>
        </div>
    </div>

    <!-- Related Docs Tab Pane -->
    <div id="pane-related" class="p-4 text-xs bg-gradient-to-b from-white to-[#FFF9FB]">
        @if(isset($relatedDocs) && count($relatedDocs) > 0)
            @php
                $chatterRole = session('active_role', auth()->user()->role ?? 'Sales');
                $accessibleScRoutes = collect(\App\Services\RoleAccessService::getAccessibleShortcuts($chatterRole))->pluck('route')->all();
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($relatedDocs as $doc)
                    @php
                        $docUrl = null;
                        $targetRoute = null;
                        $docLabel = 'Doc';
                        $docTypeLabel = 'Document';

                        if (!empty($doc->order_number)) {
                            $targetRoute = 's05.sales';
                            $docUrl = route('s05.sales', ['order' => $doc->order_number]);
                            $docLabel = $doc->order_number;
                            $docTypeLabel = 'Sales Order';
                        } elseif (!empty($doc->do_number)) {
                            $targetRoute = 's07.delivery';
                            $docUrl = route('s07.delivery', ['do' => $doc->do_number]);
                            $docLabel = $doc->do_number;
                            $docTypeLabel = 'Delivery Order';
                        } elseif (!empty($doc->invoice_number) && empty($doc->return_number)) {
                            $targetRoute = 's08.invoice';
                            $docUrl = route('s08.invoice', ['inv' => $doc->invoice_number]);
                            $docLabel = $doc->invoice_number;
                            $docTypeLabel = 'Customer Invoice';
                        } elseif (!empty($doc->po_number) && empty($doc->receipt_number) && empty($doc->bill_number)) {
                            $targetRoute = 's10.purchase';
                            $docUrl = route('s10.purchase', ['po' => $doc->po_number]);
                            $docLabel = $doc->po_number;
                            $docTypeLabel = 'Purchase Order';
                        } elseif (!empty($doc->receipt_number) && empty($doc->bill_number)) {
                            $targetRoute = 's11.receipt';
                            $docUrl = route('s11.receipt', ['rec' => $doc->receipt_number]);
                            $docLabel = $doc->receipt_number;
                            $docTypeLabel = 'Purchase Receipt';
                        } elseif (!empty($doc->bill_number)) {
                            $targetRoute = 's12.bill';
                            $docUrl = route('s12.bill', ['bill' => $doc->bill_number]);
                            $docLabel = $doc->bill_number;
                            $docTypeLabel = 'Vendor Bill';
                        } elseif (!empty($doc->return_number)) {
                            $targetRoute = 's15.return';
                            $docUrl = route('s15.return', ['ret' => $doc->return_number]);
                            $docLabel = $doc->return_number;
                            $docTypeLabel = 'Sales Return';
                        } elseif (!empty($doc->treatment_number)) {
                            $targetRoute = 's17.treatment';
                            $docUrl = route('s17.treatment', ['trt' => $doc->treatment_number]);
                            $docLabel = $doc->treatment_number;
                            $docTypeLabel = 'Beauty Treatment';
                        } elseif (!empty($doc->move_number)) {
                            $docLabel = $doc->move_number . ' (' . ($doc->reference_doc_id ?? '') . ')';
                            $docTypeLabel = $doc->reference_doc_type ?? 'Stock Move';
                        }

                        $canOpenDoc = $docUrl && $targetRoute && in_array($targetRoute, $accessibleScRoutes, true);
                    @endphp
                    @if($canOpenDoc)
                        <a href="{{ $docUrl }}" class="p-3 bg-gradient-to-br from-white to-[#FFF3F7] border border-[#F2C6D8] rounded-lg flex items-center justify-between hover:from-[#FFF0F5] hover:to-[#FADADD]/60 hover:border-[#E879A6] transition group block shadow-xs">
                            <div>
                                <div class="text-[10px] uppercase tracking-wider text-rose-400 font-semibold">{{ $docTypeLabel }}</div>
                                <div class="font-bold text-rose-950 group-hover:text-rose-700">{{ $docLabel }}</div>
                                <div class="text-rose-700/80">
                                    Status: <span class="font-semibold text-rose-700">{{ $doc->status ?? 'Done' }}</span>
                                </div>
                            </div>
                            <span class="text-rose-400 group-hover:text-rose-600 transition"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                        </a>
                    @else
                        <div class="p-3 bg-[#FFF8FA] border border-pink-100 rounded-lg flex items-center justify-between">
                            <div>
                                <div class="text-[10px] uppercase tracking-wider text-rose-400 font-semibold">{{ $docTypeLabel }}</div>
                                <div class="font-bold text-gray-800">{{ $docLabel }}</div>
                                <div class="text-gray-500">
                                    Status: <span class="font-semibold text-rose-700">{{ $doc->status ?? 'Done' }}</span>
                                </div>
                            </div>
                            <span class="text-pink-300" title="Divisi lain"><i class="fa-solid fa-lock"></i></span>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="text-rose-400/80 italic py-2 text-center">No related documents linked to this record yet.</div>
        @endif
    </div>

    <!-- Log Notes Tab Pane -->
    <div id="pane-notes" class="p-4 text-xs hidden bg-gradient-to-b from-white to-[#FFF9FB]">
        @if(isset($logNotes) && count($logNotes) > 0)
            <div class="space-y-3">
                @foreach($logNotes as $note)
                    <div class="p-3 bg-[#FFF2F7] border-l-4 border-[#E879A6] rounded-r-lg">
                        <div class="flex items-center justify-between text-rose-600 mb-1">
                            <span class="font-bold text-rose-950">{{ $note->author }}</span>
                            <span>{{ $note->created_at }}</span>
                        </div>
                        <div class="text-gray-800">{{ $note->message }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-rose-400/80 italic py-2 text-center">No log notes recorded for this document.</div>
        @endif
    </div>

    @if($canViewAuditTrail)
    <!-- Audit / History Tab Pane -->
    <div id="pane-audit" class="p-4 text-xs hidden bg-gradient-to-b from-white to-[#FFF9FB]">
        @if(isset($auditLogs) && count($auditLogs) > 0)
            <div class="overflow-x-auto rounded-lg border border-pink-100">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gradient-to-r from-[#FFF0F5] to-[#FADADD]/60 text-rose-900 border-b border-pink-200">
                            <th class="p-2.5">Timestamp</th>
                            <th class="p-2.5">Actor</th>
                            <th class="p-2.5">Role</th>
                            <th class="p-2.5">Action</th>
                            <th class="p-2.5">Target</th>
                            <th class="p-2.5">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditLogs as $log)
                            <tr class="border-b border-pink-50 hover:bg-[#FFF3F7]/70 transition">
                                <td class="p-2.5 text-gray-500">{{ $log->created_at }}</td>
                                <td class="p-2.5 font-medium text-rose-950">{{ $log->actor }}</td>
                                <td class="p-2.5"><span class="px-2 py-0.5 rounded-full bg-[#FADADD]/75 border border-pink-200 text-rose-900 font-semibold">{{ $log->role }}</span></td>
                                <td class="p-2.5">
                                    <span class="font-bold {{ str_contains($log->action, 'Blocked') || str_contains($log->action, 'Denied') ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="p-2.5 font-medium text-gray-800">{{ $log->document_type }} #{{ $log->document_id }}</td>
                                <td class="p-2.5 text-gray-600">{{ $log->details }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-rose-400/80 italic py-2 text-center">No audit trail records found.</div>
        @endif
    </div>
    @endif
</div>

<script>
    function switchChatterTab(tab) {
        const relPane = document.getElementById('pane-related');
        const notesPane = document.getElementById('pane-notes');
        const auditPane = document.getElementById('pane-audit');
        if (relPane) relPane.classList.add('hidden');
        if (notesPane) notesPane.classList.add('hidden');
        if (auditPane) auditPane.classList.add('hidden');

        const relBtn = document.getElementById('tab-btn-related');
        const notesBtn = document.getElementById('tab-btn-notes');
        const auditBtn = document.getElementById('tab-btn-audit');
        if (relBtn) relBtn.className = "py-3 border-b-2 border-transparent text-rose-700/70 hover:text-rose-900 focus:outline-none";
        if (notesBtn) notesBtn.className = "py-3 border-b-2 border-transparent text-rose-700/70 hover:text-rose-900 focus:outline-none";
        if (auditBtn) auditBtn.className = "py-3 border-b-2 border-transparent text-rose-700/70 hover:text-rose-900 focus:outline-none";

        const activePane = document.getElementById('pane-' + tab);
        const activeBtn = document.getElementById('tab-btn-' + tab);
        if (activePane) activePane.classList.remove('hidden');
        if (activeBtn) activeBtn.className = "py-3 border-b-2 border-[#D95386] text-rose-900 font-bold focus:outline-none";
    }
</script>
