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
@endphp

<!-- Floating Right-Edge Side Trigger Tab ("Small System on the Side") -->
<button
    type="button"
    id="access-drawer-trigger"
    onclick="openAccessSideDrawer()"
    class="fixed right-0 top-1/3 z-40 bg-gradient-to-b from-[#FADADD] to-[#F4B8CE] hover:from-[#F8C8DC] hover:to-[#EC9BB8] text-rose-950 shadow-xl rounded-l-xl px-2.5 py-3.5 border border-r-0 border-[#EDA4C0] transition flex flex-col items-center space-y-2 group focus:outline-none"
    title="{{ $isSuperDrawer ? 'Master Admin Access Approvals' : 'Request Cross-Division Access' }}"
>
    <div class="relative">
        <i class="fa-solid {{ $isSuperDrawer ? 'fa-user-check text-emerald-700' : 'fa-key text-rose-600' }} text-sm group-hover:scale-110 transition"></i>
        @if($drawerBadgeCount > 0)
            <span class="absolute -top-2 -right-2.5 min-w-[16px] h-4 px-1 rounded-full {{ $isSuperDrawer ? 'bg-rose-500 text-white' : 'bg-amber-400 text-slate-900' }} text-[10px] font-extrabold flex items-center justify-center shadow">
                {{ $drawerBadgeCount }}
            </span>
        @endif
    </div>
    <span class="text-[10px] font-bold tracking-wider uppercase [writing-mode:vertical-rl] rotate-180 text-rose-950">
        {{ $isMasterAdminDrawer ? 'Approvals' : ($isSuperDrawer ? 'Access Queue' : 'Request Access') }}
    </span>
</button>

<!-- Slide-Over Side Panel Backdrop & Drawer -->
<div
    id="access-side-drawer-overlay"
    class="fixed inset-0 z-[90] {{ $autoOpenDrawer ? 'flex' : 'hidden' }} justify-end bg-slate-900/40 backdrop-blur-[1px]"
    onclick="if(event.target === this) closeAccessSideDrawer()"
>
    <aside class="w-full max-w-[92vw] sm:max-w-md xl:max-w-lg bg-white h-full shadow-2xl border-l border-[#F2B6CE] flex flex-col overflow-hidden">
        <!-- Drawer Header -->
        <div class="bg-gradient-to-r from-[#FFF0F5] via-[#FADADD] to-[#F8C8DC] border-b border-[#F2B6CE] text-rose-950 px-5 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <span class="w-8 h-8 rounded-lg bg-white/85 border border-pink-200 flex items-center justify-center text-rose-600 shadow-sm">
                    <i class="fa-solid {{ $isSuperDrawer ? 'fa-shield-halved' : 'fa-paper-plane' }}"></i>
                </span>
                <div>
                    <h3 class="text-sm font-bold leading-tight text-rose-950">
                        {{ $isMasterAdminDrawer ? 'Master Admin: Access Approval' : ($isSuperDrawer ? 'Cross-Division Access Queue' : 'Request Cross-Division Access') }}
                    </h3>
                    <p class="text-[11px] text-rose-700/90">
                        {{ $isMasterAdminDrawer ? 'Review & approve staff division requests' : ($isSuperDrawer ? 'Monitored by Owner · Approved by Master Admin' : 'Forward request to Master Admin for approval') }}
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeAccessSideDrawer()" class="text-rose-700 hover:text-rose-950 p-1.5 rounded-lg hover:bg-white/60 transition focus:outline-none">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Drawer Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-5 space-y-5 text-xs bg-gradient-to-b from-[#FFF7FA] to-[#FFFBFD]">
            @if(!$isSuperDrawer)
                <!-- STAFF VIEW: Request Form to Master Admin -->
                <div class="bg-white rounded-xl border border-[#F2C6D8] p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-rose-950 text-xs flex items-center">
                            <i class="fa-solid fa-lock-open text-rose-500 mr-1.5"></i>
                            Forward Request to Master Admin
                        </span>
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-[#FADADD]/80 border border-pink-200 text-rose-900 font-semibold">
                            Divisi {{ $drawerRole }}
                        </span>
                    </div>
                    <p class="text-[11px] text-rose-700/80 mb-3">
                        Need temporary access to another division's module or document? Submit a request below — access activates once <strong>Master Admin</strong> approves it.
                    </p>

                    @php
                        $selectableApps = collect($requestableApps)->where('is_pending', false);
                    @endphp

                    @if($selectableApps->isNotEmpty())
                        <form action="{{ route('access.request.submit') }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block font-semibold text-rose-900 mb-1">Target Division Module</label>
                                <select name="app_key" required class="w-full border border-pink-200 rounded-lg p-2 bg-[#FFF9FB] text-rose-950 focus:ring-2 focus:ring-[#E879A6] focus:outline-none">
                                    @foreach($selectableApps as $aKey => $aInfo)
                                        <option value="{{ $aKey }}">{{ $aInfo['title'] }} ({{ $aInfo['division'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-rose-900 mb-1">Reason / Related Document</label>
                                <textarea
                                    name="reason"
                                    rows="2"
                                    required
                                    maxlength="300"
                                    placeholder="Example: Need to inspect Stock / Delivery WH/OUT/00006 for customer inquiry"
                                    class="w-full border border-pink-200 rounded-lg p-2 bg-[#FFF9FB] text-rose-950 placeholder-rose-300 focus:ring-2 focus:ring-[#E879A6] focus:outline-none"
                                ></textarea>
                            </div>
                            <button type="submit" class="w-full odoo-btn-primary font-bold py-2 px-3 rounded-lg shadow-sm transition flex items-center justify-center space-x-1.5">
                                <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                <span>Forward Request to Master Admin</span>
                            </button>
                        </form>
                    @else
                        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px]">
                            All other division modules are already either granted to {{ $drawerRole }} or awaiting Master Admin approval.
                        </div>
                    @endif
                </div>

                <!-- STAFF VIEW: Active Approved Access -->
                @if($myActiveGrants->isNotEmpty())
                    <div class="bg-white rounded-xl border border-emerald-200 p-4 shadow-sm">
                        <h4 class="font-bold text-emerald-900 uppercase tracking-wider text-[10px] mb-2.5 flex items-center">
                            <i class="fa-solid fa-circle-check text-emerald-600 mr-1.5"></i>
                            Approved Cross-Division Access ({{ $myActiveGrants->count() }})
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
                                            Approved by {{ $grant->granted_by }}
                                            @if($grant->expires_at)
                                                &bull; <span class="text-amber-700 font-semibold">Expires {{ $grant->expires_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($gApp)
                                        <a href="{{ route($gApp['route']) }}" class="px-2.5 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition">
                                            Open <i class="fa-solid fa-arrow-right ml-0.5 text-[9px]"></i>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- STAFF VIEW: Request Status History -->
                <div class="bg-white rounded-xl border border-[#F2C6D8] p-4 shadow-sm">
                    <h4 class="font-bold text-rose-700 uppercase tracking-wider text-[10px] mb-2.5">
                        My Division Requests ({{ $myDivisionRequests->count() }})
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
                                    <span class="font-bold text-rose-950">{{ $rApp['title'] }} <span class="text-rose-400 font-normal">({{ $rApp['division'] }})</span></span>
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
                                            <button type="submit" class="text-rose-600 hover:underline font-semibold">Cancel Request</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-rose-400/80 italic py-4">No cross-division access requests submitted yet.</div>
                        @endforelse
                    </div>
                </div>
            @else
                <!-- MASTER ADMIN / OWNER VIEW: Pending Staff Requests -->
                <div class="bg-white rounded-xl border border-[#F2C6D8] p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-bold text-rose-950 uppercase tracking-wider text-[11px] flex items-center">
                            <i class="fa-solid fa-inbox text-rose-500 mr-1.5"></i>
                            Pending Staff Requests ({{ $pendingAdminRequests->count() }})
                        </h4>
                        @if($pendingAdminRequests->count() > 0)
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold border border-rose-200">
                                Needs Master Admin Approval
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
                                    Requests access to: <strong class="text-rose-800">{{ $rApp['title'] }}</strong>
                                    <span class="text-rose-500">({{ $rApp['division'] }})</span>
                                </div>
                                <div class="p-2 rounded-lg bg-white border border-pink-100 text-gray-700 text-[11px]">
                                    <span class="text-rose-400 font-semibold block text-[10px]">Reason:</span>
                                    {{ $req->reason }}
                                </div>

                                @if($isMasterAdminDrawer)
                                    <div class="pt-1 space-y-2">
                                        <div class="grid grid-cols-2 gap-2">
                                            <select
                                                id="admin-dur-{{ $req->id }}"
                                                class="border border-pink-200 rounded-lg px-2 py-1.5 bg-white text-rose-950 text-[11px] focus:outline-none focus:ring-1 focus:ring-[#E879A6]"
                                            >
                                                <option value="1">Duration: 1 Hour</option>
                                                <option value="4">Duration: 4 Hours</option>
                                                <option value="8">Duration: 8 Hours</option>
                                                <option value="24" selected>Duration: 24 Hours</option>
                                                <option value="72">Duration: 3 Days</option>
                                                <option value="168">Duration: 7 Days</option>
                                                <option value="">Until Deleted</option>
                                            </select>
                                            <input
                                                type="text"
                                                id="admin-note-{{ $req->id }}"
                                                maxlength="255"
                                                placeholder="Optional note..."
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
                            <div class="text-center text-rose-400/80 italic py-4">No pending staff access requests.</div>
                        @endforelse
                    </div>
                </div>

                <!-- MASTER ADMIN / OWNER VIEW: Active Cross-Division Grants (Delete Access) -->
                <div class="bg-white rounded-xl border border-[#F2C6D8] p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-2.5">
                        <h4 class="font-bold text-rose-900 uppercase tracking-wider text-[10px]">
                            Allocated Temporary Access ({{ $allActiveGrants->count() }})
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
                                        By {{ $g->granted_by }}
                                        @if($g->expires_at)
                                            &bull; <span class="text-amber-700 font-semibold">Ends {{ $g->expires_at->diffForHumans() }}</span>
                                        @else
                                            &bull; Until deleted
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
                            <div class="text-center text-rose-400/80 italic py-2">No active cross-division access allocations.</div>
                        @endforelse
                    </div>
                </div>

                <!-- MASTER ADMIN / OWNER VIEW: Recent Processed History -->
                @if($recentAdminRequests->isNotEmpty())
                    <div class="bg-white rounded-xl border border-[#F2C6D8] p-4 shadow-sm">
                        <h4 class="font-bold text-rose-700 uppercase tracking-wider text-[10px] mb-2.5">
                            Recent Decisions
                        </h4>
                        <div class="space-y-2">
                            @foreach($recentAdminRequests as $req)
                                <div class="p-2.5 rounded-lg bg-[#FFF8FA] border border-pink-100 flex items-center justify-between">
                                    <div>
                                        <div class="font-semibold text-rose-950">
                                            {{ $req->requester_role }} &rarr; {{ $allDrawerApps[$req->app_key]['title'] ?? $req->app_key }}
                                        </div>
                                        <div class="text-[10px] text-rose-400">Reviewed by {{ $req->reviewed_by }}</div>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $req->status === 'Approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $req->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </aside>
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

