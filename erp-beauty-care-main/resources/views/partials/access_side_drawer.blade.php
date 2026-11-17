@php
    $drawerRole = session('active_role', auth()->user()->role ?? 'Sales');
    $isSuperDrawer = \App\Services\RoleAccessService::isSuperRole($drawerRole);
    $isMasterAdminDrawer = ($drawerRole === 'Admin');
    $allDrawerApps = \App\Services\RoleAccessService::getAllApps();

    \App\Services\RoleAccessService::purgeExpiredGrants();

    $pendingAdminRequests = collect();
    $recentAdminRequests = collect();
    $myDivisionRequests = collect();
    $myActiveGrants = collect();
    $allActiveGrants = collect();
    $requestableApps = [];

    if (\Illuminate\Support\Facades\Schema::hasTable('access_requests')) {
        if ($isSuperDrawer) {
            $pendingAdminRequests = \App\Models\AccessRequest::where('status', 'Pending')->orderByDesc('id')->get();
            $recentAdminRequests = \App\Models\AccessRequest::where('status', '!=', 'Pending')->orderByDesc('id')->take(8)->get();
        } else {
            $myDivisionRequests = \App\Models\AccessRequest::where('requester_role', $drawerRole)->orderByDesc('id')->take(10)->get();
        }
    }

    if (\Illuminate\Support\Facades\Schema::hasTable('access_grants')) {
        if ($isSuperDrawer) {
            $allActiveGrants = \App\Models\AccessGrant::orderByDesc('id')->get();
        } else {
            $myActiveGrants = \App\Models\AccessGrant::where('grantee_role', $drawerRole)->orderByDesc('id')->get();
        }
    }

    if (!$isSuperDrawer) {
        $ownedAppKeys = \App\Services\RoleAccessService::getBaseAppKeys($drawerRole);
        $grantedKeys = $myActiveGrants->pluck('app_key')->all();
        $pendingKeys = $myDivisionRequests->where('status', 'Pending')->pluck('app_key')->all();

        foreach ($allDrawerApps as $aKey => $aInfo) {
            if (!in_array($aKey, $ownedAppKeys, true) && !in_array($aKey, $grantedKeys, true)) {
                $aInfo['is_pending'] = in_array($aKey, $pendingKeys, true);
                $requestableApps[$aKey] = $aInfo;
            }
        }
    }

    $drawerBadgeCount = $isSuperDrawer
        ? $pendingAdminRequests->count()
        : $myDivisionRequests->where('status', 'Pending')->count();

    $autoOpenDrawer = session('open_access_drawer') || request()->boolean('open_access_drawer');
    $isEnDrawer = app()->getLocale() === 'en';
@endphp

<!-- Centered Pop-Up Modal Dialog (Request Access / Master Admin Approvals) -->
<div
    id="access-side-drawer-overlay"
    class="fixed inset-0 z-[90] {{ $autoOpenDrawer ? 'flex' : 'hidden' }} items-center justify-center p-4 sm:p-6 bg-rose-950/40 backdrop-blur-[2px]"
    onclick="if(event.target === this) closeAccessSideDrawer()"
>
    <div class="w-full max-w-3xl max-h-[88vh] bg-white rounded-2xl shadow-2xl border border-[#F2B6CE] flex flex-col overflow-hidden ring-1 ring-rose-900/5">
        <!-- Pop-Up Header -->
        <div class="bg-gradient-to-r from-[#FFF0F5] via-[#FADADD] to-[#F8C8DC] border-b border-[#F2B6CE] text-rose-950 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="w-9 h-9 rounded-xl bg-white/90 border border-pink-200 flex items-center justify-center text-rose-600 shadow-sm">
                    <i class="fa-solid {{ $isSuperDrawer ? 'fa-shield-halved' : 'fa-paper-plane' }} text-sm"></i>
                </span>
                <div>
                    <h3 class="text-sm sm:text-base font-bold leading-tight text-rose-950">
                        {{ $isMasterAdminDrawer ? ($isEnDrawer ? 'Master Admin: Access Approval' : 'Master Admin: Persetujuan Akses') : ($isSuperDrawer ? 'Cross-Division Access Queue' : ($isEnDrawer ? 'Request Cross-Division Access' : 'Ajukan Akses Lintas Divisi')) }}
                    </h3>
                    <p class="text-[11px] text-rose-700/90">
                        {{ $isMasterAdminDrawer ? ($isEnDrawer ? 'Review & approve staff division requests' : 'Tinjau & setujui pengajuan akses divisi staf') : ($isSuperDrawer ? 'Monitored by Owner · Approved by Master Admin' : ($isEnDrawer ? 'Forward request to Master Admin for approval' : 'Teruskan pengajuan ke Master Admin untuk persetujuan')) }}
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeAccessSideDrawer()" class="text-rose-700 hover:text-rose-950 p-2 rounded-lg hover:bg-white/60 transition focus:outline-none" title="{{ $isEnDrawer ? 'Close Pop-Up' : 'Tutup Pop-Up' }}">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Pop-Up Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-5 sm:p-6 text-xs bg-gradient-to-b from-[#FFF7FA] to-[#FFFBFD]">
            @if(!$isSuperDrawer)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
                    <!-- LEFT COLUMN: Request Form to Master Admin -->
                    <div class="bg-white rounded-xl border border-[#F2C6D8] p-4 sm:p-5 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-rose-950 text-xs flex items-center">
                                <i class="fa-solid fa-lock-open text-rose-500 mr-1.5"></i>
                                {{ $isEnDrawer ? 'Forward Request to Master Admin' : 'Kirim Pengajuan ke Master Admin' }}
                            </span>
                            <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-[#FADADD]/80 border border-pink-200 text-rose-900 font-semibold">
                                {{ $isEnDrawer ? 'Division ' : 'Divisi ' }}{{ $drawerRole }}
                            </span>
                        </div>
                        <p class="text-[11px] text-rose-700/80 mb-3.5">
                            {!! $isEnDrawer ? "Need temporary access to another division's module or document? Submit a request below — access activates once <strong>Master Admin</strong> approves it." : "Butuh akses sementara ke modul atau dokumen divisi lain? Kirim pengajuan di bawah ini — akses aktif setelah disetujui oleh <strong>Master Admin</strong>." !!}
                        </p>

                        @php
                            $selectableApps = collect($requestableApps)->where('is_pending', false);
                        @endphp

                        @if($selectableApps->isNotEmpty())
                            <form action="{{ route('access.request.submit') }}" method="POST" class="space-y-3.5">
                                @csrf
                                <div>
                                    <label class="block font-semibold text-rose-900 mb-1">{{ $isEnDrawer ? 'Target Module' : 'Modul Tujuan' }}</label>
                                    <select name="app_key" required class="w-full border border-pink-200 rounded-lg p-2.5 bg-[#FFF9FB] text-rose-950 focus:ring-2 focus:ring-[#E879A6] focus:outline-none">
                                        @foreach($selectableApps as $aKey => $aInfo)
                                            <option value="{{ $aKey }}">{{ $aInfo['title'] }} — {{ $aInfo['subtitle'] ?? 'ERP Module' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-semibold text-rose-900 mb-1">{{ $isEnDrawer ? 'Reason / Related Document' : 'Alasan / Dokumen Terkait' }}</label>
                                    <textarea
                                        name="reason"
                                        rows="3"
                                        required
                                        maxlength="300"
                                        placeholder="{{ $isEnDrawer ? 'Example: Need to inspect Stock / Delivery WH/OUT/00006 for customer inquiry' : 'Contoh: Perlu mengecek Stok / Delivery WH/OUT/00006 untuk pertanyaan pelanggan' }}"
                                        class="w-full border border-pink-200 rounded-lg p-2.5 bg-[#FFF9FB] text-rose-950 placeholder-rose-300 focus:ring-2 focus:ring-[#E879A6] focus:outline-none"
                                    ></textarea>
                                </div>
                                <button type="submit" class="w-full odoo-btn-primary font-bold py-2.5 px-3 rounded-lg shadow-sm transition flex items-center justify-center space-x-1.5">
                                    <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                    <span>{{ $isEnDrawer ? 'Forward Request to Master Admin' : 'Kirim Pengajuan ke Master Admin' }}</span>
                                </button>
                            </form>
                        @else
                            <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px]">
                                {{ $isEnDrawer ? "All other division modules are already either granted to {$drawerRole} or awaiting Master Admin approval." : "Seluruh modul divisi lain sudah diberikan ke {$drawerRole} atau sedang menunggu persetujuan Master Admin." }}
                            </div>
                        @endif
                    </div>

                    <!-- RIGHT COLUMN: Active Grants + Request History -->
                    <div class="space-y-4">
                        @if($myActiveGrants->isNotEmpty())
                            <div class="bg-white rounded-xl border border-emerald-200 p-4 shadow-sm">
                                <h4 class="font-bold text-emerald-900 uppercase tracking-wider text-[10px] mb-2.5 flex items-center">
                                    <i class="fa-solid fa-circle-check text-emerald-600 mr-1.5"></i>
                                    {{ $isEnDrawer ? 'Approved Cross-Division Access' : 'Akses Lintas Divisi Aktif' }} ({{ $myActiveGrants->count() }})
                                </h4>
                                <div class="space-y-2">
                                    @foreach($myActiveGrants as $grant)
                                        @php
                                            $gApp = $allDrawerApps[$grant->app_key] ?? null;
                                        @endphp
                                        <div class="p-2.5 rounded-lg bg-emerald-50/70 border border-emerald-200 flex items-center justify-between">
                                            <div>
                                                <div class="font-bold text-gray-800">{{ $gApp['title'] ?? $grant->app_key }}</div>
                                                <div class="text-[10px] text-gray-500">
                                                    {{ $isEnDrawer ? 'Approved by' : 'Disetujui oleh' }} {{ $grant->granted_by }}
                                                    @if($grant->expires_at)
                                                        &bull; <span class="text-amber-700 font-semibold">{{ $isEnDrawer ? 'Expires' : 'Berakhir' }} {{ $grant->expires_at->diffForHumans() }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($gApp)
                                                <a href="{{ route($gApp['route']) }}" class="px-2.5 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition">
                                                    {{ $isEnDrawer ? 'Open' : 'Buka' }} <i class="fa-solid fa-arrow-right ml-0.5 text-[9px]"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Request Status History -->
                        <div class="bg-white rounded-xl border border-[#F2C6D8] p-4 shadow-sm">
                            <h4 class="font-bold text-rose-700 uppercase tracking-wider text-[10px] mb-2.5">
                                {{ $isEnDrawer ? 'My Division Requests' : 'Riwayat Pengajuan Divisi Saya' }} ({{ $myDivisionRequests->count() }})
                            </h4>
                            <div class="space-y-2.5">
                                @forelse($myDivisionRequests as $req)
                                    @php
                                        $rApp = $allDrawerApps[$req->app_key] ?? ['title' => $req->app_key, 'division' => '-'];
                                        $badgeClass = match($req->status) {
                                            'Approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'Rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
                                            'Revoked', 'Expired' => 'bg-gray-200 text-gray-700 border-gray-300',
                                            default    => 'bg-amber-100 text-amber-800 border-amber-200',
                                        };
                                    @endphp
                                    <div class="p-3 rounded-lg bg-[#FFF8FA] border border-pink-100 space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-rose-950">{{ $rApp['title'] }}</span>
                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold border {{ $badgeClass }}">
                                                {{ $req->status === 'Pending' ? 'Pending Master Admin' : $req->status }}
                                            </span>
                                        </div>
                                        <div class="text-gray-600 text-[11px]">“{{ $req->reason }}”</div>
                                        @if($req->review_note)
                                            <div class="text-[10px] text-rose-900 bg-[#FFF0F5] px-2 py-1 rounded border border-pink-200">
                                                <strong>Master Admin Note:</strong> {{ $req->review_note }}
                                            </div>
                                        @endif
                                        <div class="flex items-center justify-between pt-1 text-[10px] text-rose-400">
                                            <span>{{ $req->created_at }}</span>
                                            @if($req->status === 'Pending')
                                                <form action="{{ route('access.request.cancel', $req->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-rose-600 hover:underline font-semibold">{{ $isEnDrawer ? 'Cancel Request' : 'Batalkan' }}</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-rose-400/80 italic py-4">{{ $isEnDrawer ? 'No cross-division access requests submitted yet.' : 'Belum ada pengajuan akses lintas divisi.' }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
                    <!-- LEFT COLUMN: Pending Staff Requests -->
                    <div class="bg-white rounded-xl border border-[#F2C6D8] p-4 sm:p-5 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-rose-950 uppercase tracking-wider text-[11px] flex items-center">
                                <i class="fa-solid fa-inbox text-rose-500 mr-1.5"></i>
                                {{ $isEnDrawer ? 'Pending Staff Requests' : 'Pengajuan Staf Menunggu' }} ({{ $pendingAdminRequests->count() }})
                            </h4>
                            @if($pendingAdminRequests->count() > 0)
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold border border-rose-200">
                                    {{ $isEnDrawer ? 'Needs Master Admin Approval' : 'Butuh Persetujuan Master Admin' }}
                                </span>
                            @endif
                        </div>

                        <div class="space-y-3">
                            @forelse($pendingAdminRequests as $req)
                                @php
                                    $rApp = $allDrawerApps[$req->app_key] ?? ['title' => $req->app_key, 'division' => '-'];
                                @endphp
                                <div class="p-3.5 rounded-xl bg-gradient-to-br from-[#FFF5F8] to-[#FADADD]/50 border border-pink-200 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-bold text-rose-950">{{ $req->requester_name }}</span>
                                            <span class="ml-1 px-1.5 py-0.5 rounded bg-white border border-pink-200 text-rose-800 font-bold text-[10px]">{{ $req->requester_role }}</span>
                                        </div>
                                        <span class="text-[10px] text-rose-400">{{ $req->created_at }}</span>
                                    </div>
                                    <div class="text-xs">
                                        {{ $isEnDrawer ? 'Requests access to:' : 'Meminta akses ke:' }} <strong class="text-rose-800">{{ $rApp['title'] }}</strong>
                                        <span class="text-rose-500">({{ $rApp['division'] }})</span>
                                    </div>
                                    <div class="p-2 rounded-lg bg-white border border-pink-100 text-gray-700 text-[11px]">
                                        <span class="text-rose-400 font-semibold block text-[10px]">{{ $isEnDrawer ? 'Reason:' : 'Alasan:' }}</span>
                                        {{ $req->reason }}
                                    </div>

                                    @if($isMasterAdminDrawer)
                                        <div class="pt-1 space-y-2">
                                            <div class="grid grid-cols-2 gap-2">
                                                <select
                                                    id="admin-dur-{{ $req->id }}"
                                                    class="border border-pink-200 rounded-lg px-2 py-1.5 bg-white text-rose-950 text-[11px] focus:outline-none focus:ring-1 focus:ring-[#E879A6]"
                                                >
                                                    <option value="1">{{ $isEnDrawer ? 'Duration: 1 Hour' : 'Durasi: 1 Jam' }}</option>
                                                    <option value="4">{{ $isEnDrawer ? 'Duration: 4 Hours' : 'Durasi: 4 Jam' }}</option>
                                                    <option value="8">{{ $isEnDrawer ? 'Duration: 8 Hours' : 'Durasi: 8 Jam' }}</option>
                                                    <option value="24" selected>{{ $isEnDrawer ? 'Duration: 24 Hours' : 'Durasi: 24 Jam' }}</option>
                                                    <option value="72">{{ $isEnDrawer ? 'Duration: 3 Days' : 'Durasi: 3 Hari' }}</option>
                                                    <option value="168">{{ $isEnDrawer ? 'Duration: 7 Days' : 'Durasi: 7 Hari' }}</option>
                                                    <option value="">{{ $isEnDrawer ? 'Until Deleted' : 'Sampai Dihapus' }}</option>
                                                </select>
                                                <input
                                                    type="text"
                                                    id="admin-note-{{ $req->id }}"
                                                    maxlength="255"
                                                    placeholder="{{ $isEnDrawer ? 'Optional note...' : 'Catatan opsional...' }}"
                                                    class="border border-pink-200 rounded-lg px-2.5 py-1.5 bg-white text-rose-950 placeholder-rose-300 text-[11px] focus:outline-none focus:ring-1 focus:ring-[#E879A6]"
                                                >
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <form action="{{ route('access.request.approve', $req->id) }}" method="POST" onsubmit="this.querySelector('input[name=review_note]').value = document.getElementById('admin-note-{{ $req->id }}').value; this.querySelector('input[name=duration_hours]').value = document.getElementById('admin-dur-{{ $req->id }}').value;">
                                                    @csrf
                                                    <input type="hidden" name="review_note" value="">
                                                    <input type="hidden" name="duration_hours" value="24">
                                                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1.5 px-3 rounded-lg transition flex items-center justify-center space-x-1">
                                                        <i class="fa-solid fa-check text-[10px]"></i>
                                                        <span>Approve</span>
                                                    </button>
                                                </form>
                                                <form action="{{ route('access.request.reject', $req->id) }}" method="POST" onsubmit="this.querySelector('input[name=review_note]').value = document.getElementById('admin-note-{{ $req->id }}').value;">
                                                    @csrf
                                                    <input type="hidden" name="review_note" value="">
                                                    <button type="submit" class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-1.5 px-3 rounded-lg transition flex items-center justify-center space-x-1">
                                                        <i class="fa-solid fa-xmark text-[10px]"></i>
                                                        <span>Reject</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-[10px] text-rose-700 italic pt-1">
                                            Awaiting approval from Master Admin (admin@gmail.com).
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center text-rose-400/80 italic py-4">{{ $isEnDrawer ? 'No pending staff access requests.' : 'Tidak ada pengajuan akses staf yang menunggu.' }}</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Active Cross-Division Grants + Recent Processed History -->
                    <div class="space-y-4">
                        <div class="bg-white rounded-xl border border-[#F2C6D8] p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2.5">
                                <h4 class="font-bold text-rose-900 uppercase tracking-wider text-[10px]">
                                    {{ $isEnDrawer ? 'Allocated Temporary Access' : 'Alokasi Akses Sementara Aktif' }} ({{ $allActiveGrants->count() }})
                                </h4>
                                @if($allActiveGrants->isNotEmpty())
                                    <form action="{{ route('access.revoke.all') }}" method="POST" onsubmit="return confirm('Delete all active temporary access allocations?');">
                                        @csrf
                                        <input type="hidden" name="from_drawer" value="1">
                                        <button type="submit" class="text-[10px] bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold px-2 py-0.5 rounded border border-rose-200 transition">
                                            <i class="fa-solid fa-trash-can mr-0.5"></i>Delete All
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <div class="space-y-2">
                                @forelse($allActiveGrants as $g)
                                    <div class="p-2.5 rounded-lg bg-[#FFF8FA] border border-pink-100 flex items-center justify-between gap-2">
                                        <div>
                                            <div>
                                                <strong class="text-rose-900">{{ $g->grantee_role }}</strong>
                                                <i class="fa-solid fa-arrow-right text-[9px] text-rose-400 mx-1"></i>
                                                <strong class="text-emerald-700">{{ $allDrawerApps[$g->app_key]['title'] ?? $g->app_key }}</strong>
                                            </div>
                                            <div class="text-[10px] text-rose-500">
                                                {{ $isEnDrawer ? 'By' : 'Oleh' }} {{ $g->granted_by }}
                                                @if($g->expires_at)
                                                    &bull; <span class="text-amber-700 font-semibold">{{ $isEnDrawer ? 'Ends' : 'Berakhir' }} {{ $g->expires_at->diffForHumans() }}</span>
                                                @else
                                                    &bull; {{ $isEnDrawer ? 'Until deleted' : 'Sampai dihapus' }}
                                                @endif
                                            </div>
                                        </div>
                                        <form action="{{ route('access.revoke', $g->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="from_drawer" value="1">
                                            <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white font-bold text-[11px] px-2.5 py-1 rounded-md shadow-xs transition flex items-center space-x-1">
                                                <i class="fa-solid fa-trash-can text-[9px]"></i>
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="text-center text-rose-400/80 italic py-2">{{ $isEnDrawer ? 'No active cross-division access allocations.' : 'Belum ada alokasi akses lintas divisi aktif.' }}</div>
                                @endforelse
                            </div>
                        </div>

                        @if($recentAdminRequests->isNotEmpty())
                            <div class="bg-white rounded-xl border border-[#F2C6D8] p-4 shadow-sm">
                                <h4 class="font-bold text-rose-700 uppercase tracking-wider text-[10px] mb-2.5">
                                    {{ $isEnDrawer ? 'Recent Decisions' : 'Keputusan Terbaru' }}
                                </h4>
                                <div class="space-y-2">
                                    @foreach($recentAdminRequests as $req)
                                        <div class="p-2.5 rounded-lg bg-[#FFF8FA] border border-pink-100 flex items-center justify-between">
                                            <div>
                                                <div class="font-semibold text-rose-950">
                                                    {{ $req->requester_role }} &rarr; {{ $allDrawerApps[$req->app_key]['title'] ?? $req->app_key }}
                                                </div>
                                                <div class="text-[10px] text-rose-400">{{ $isEnDrawer ? 'Reviewed by' : 'Ditinjau oleh' }} {{ $req->reviewed_by }}</div>
                                            </div>
                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $req->status === 'Approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                {{ $req->status }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Pop-Up Footer -->
        <div class="bg-[#FFF5F8] border-t border-[#F2B6CE] px-6 py-3 flex items-center justify-between text-[11px] text-rose-700/90">
            <span>
                <i class="fa-solid fa-circle-info mr-1 text-rose-400"></i>
                {!! $isEnDrawer ? 'Press <kbd class="px-1.5 py-0.5 rounded bg-white border border-pink-200 font-semibold text-rose-900 text-[10px]">Esc</kbd> or click outside to close' : 'Tekan <kbd class="px-1.5 py-0.5 rounded bg-white border border-pink-200 font-semibold text-rose-900 text-[10px]">Esc</kbd> atau klik di luar untuk menutup' !!}
            </span>
            <button type="button" onclick="closeAccessSideDrawer()" class="px-3.5 py-1.5 rounded-lg bg-white hover:bg-pink-50 border border-pink-200 text-rose-900 font-semibold shadow-xs transition">
                {{ $isEnDrawer ? 'Close Pop-Up' : 'Tutup Pop-Up' }}
            </button>
        </div>
    </div>
</div>

<script>
    function openAccessSideDrawer() {
        const el = document.getElementById('access-side-drawer-overlay');
        if (el) {
            el.classList.remove('hidden');
            el.classList.add('flex');
        }
    }

    function closeAccessSideDrawer() {
        const el = document.getElementById('access-side-drawer-overlay');
        if (el) {
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
    }
</script>

