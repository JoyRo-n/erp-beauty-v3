<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ERP Beauty Care') — PT Arunika Beauty Nusantara</title>
    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --odoo-primary: #E879A6;
            --odoo-dark: #D65C8C;
            --odoo-accent: #F48FB1;
            --odoo-bg: #FFF9FB;
            --odoo-border: #F2C6D8;
        }
        html {
            font-size: clamp(13.5px, 0.32vw + 11.8px, 18px);
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(180deg, #FFF4F8 0%, #FFFBFD 45%, #FFF2F7 100%);
            background-attachment: fixed;
            color: #3B2330;
            overflow-x: hidden;
        }
        .odoo-header {
            background: linear-gradient(90deg, #FFF0F5 0%, #FADADD 50%, #F8C8DC 100%);
            border-bottom: 1px solid #F2B6CE;
            box-shadow: 0 3px 12px -2px rgba(232, 121, 166, 0.18);
        }
        .odoo-btn-primary {
            background: linear-gradient(90deg, #EC709F 0%, #D95386 100%);
            color: white;
            box-shadow: 0 2px 6px rgba(217, 83, 134, 0.25);
        }
        .odoo-btn-primary:hover {
            background: linear-gradient(90deg, #DF5D8E 0%, #C74174 100%);
        }
        .odoo-btn-secondary {
            background-color: #FFF0F5;
            color: #831843;
            border: 1px solid #F2C6D8;
        }
        .odoo-btn-secondary:hover {
            background-color: #FADADD;
        }
        /* Global Soft Pale Pink Beauty Care Surface & Table Theme */
        main > div > div:first-child.bg-white.border {
            background: linear-gradient(90deg, #FFF3F7 0%, #FADADD 45%, #FFFFFF 100%);
            border-color: #F2C6D8;
            border-radius: 0.75rem;
        }
        main .bg-white.border-gray-200 {
            border-color: #F2D0DE;
            box-shadow: 0 2px 10px -3px rgba(232, 121, 166, 0.1);
        }
        main table thead,
        main table thead.bg-gray-100 {
            background: linear-gradient(90deg, #FFF0F5 0%, #FADADD 65%, #FFF6F9 100%) !important;
            color: #7A2048 !important;
            border-bottom: 1px solid #F2C6D8;
        }
        main table tfoot.bg-gray-50 {
            background: linear-gradient(90deg, #FFF7FA 0%, #FFF0F5 100%) !important;
        }
        main select,
        main input[type="text"],
        main input[type="number"],
        main input[type="email"],
        main input[type="date"],
        main textarea {
            border-color: #EFC2D4;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        main select:focus,
        main input[type="text"]:focus,
        main input[type="number"]:focus,
        main input[type="email"]:focus,
        main input[type="date"]:focus,
        main textarea:focus {
            border-color: #E879A6;
            box-shadow: 0 0 0 3px rgba(232, 121, 166, 0.2);
            outline: none;
        }
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #FFF5F8;
        }
        ::-webkit-scrollbar-thumb {
            background: #F4B8CE;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #E879A6;
        }
        .status-pill {
            clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 50%, calc(100% - 12px) 100%, 0 100%, 12px 50%);
        }
        .status-pill-first {
            clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 50%, calc(100% - 12px) 100%, 0 100%);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Top Odoo 17 Web Navigation Bar -->
    @php
        $currRole = session('active_role', 'Sales');
        $accessibleApps = \App\Services\RoleAccessService::getAccessibleApps($currRole);
        $activeProfile = \App\Services\RoleAccessService::getProfile($currRole);
        $roleProfiles = \App\Services\RoleAccessService::getProfiles();
        $notifList = collect();
        $notifUnread = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('app_notifications')) {
            $notifList = \App\Models\AppNotification::where('recipient_role', $currRole)->orderByDesc('id')->take(8)->get();
            $notifUnread = \App\Models\AppNotification::where('recipient_role', $currRole)->whereNull('read_at')->count();
        }
        $originSuperUser = null;
        if (session('origin_super_user_id') && !\App\Services\RoleAccessService::isSuperRole($currRole)) {
            $originSuperUser = \App\Models\User::find(session('origin_super_user_id'));
        }
        $pendingAccessReqCount = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('access_requests')) {
            $pendingAccessReqCount = \App\Services\RoleAccessService::isSuperRole($currRole)
                ? \App\Models\AccessRequest::where('status', 'Pending')->count()
                : \App\Models\AccessRequest::where('requester_role', $currRole)->where('status', 'Pending')->count();
        }
    @endphp
    <header class="odoo-header text-rose-950 min-h-[3.5rem] py-1.5 flex flex-wrap items-center justify-between gap-2 px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-14 select-none relative z-50">
        <div class="flex items-center space-x-3.5">
            <!-- App Switcher Dropdown (▦ Icon) - Dynamic by Division / Role -->
            <div class="relative" id="app-switcher-container">
                <button type="button" id="app-switcher-btn" onclick="toggleAppSwitcher(event)" class="flex items-center space-x-2 text-lg bg-white/75 hover:bg-white border border-pink-200/90 text-rose-900 px-3 py-1.5 rounded-lg shadow-sm transition focus:outline-none">
                    <i class="fa-solid fa-table-cells text-base text-rose-500"></i>
                    <span class="font-bold text-sm tracking-wide">Apps</span>
                    <i class="fa-solid fa-chevron-down text-[11px] text-rose-400"></i>
                </button>
                <div id="app-switcher-menu" class="absolute left-0 top-full mt-1.5 w-72 bg-white text-gray-800 rounded-xl shadow-2xl py-2 hidden z-50 border border-pink-200 ring-1 ring-black/5">
                    <div class="px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-rose-400 border-b border-pink-100 pb-2 mb-1 flex justify-between items-center">
                        <span>Aplikasi Divisi &bull; {{ $currRole }}</span>
                        <a href="{{ route('s01.home') }}" class="text-rose-600 hover:underline normal-case font-semibold"><i class="fa-solid fa-house mr-1"></i>Home</a>
                    </div>
                    @foreach($accessibleApps as $app)
                        <a href="{{ route($app['route']) }}" class="flex items-center px-4 py-2 hover:bg-pink-50/70 text-sm font-medium transition">
                            <span class="w-8 h-8 rounded {{ $app['bg_color'] }} {{ $app['text_color'] }} flex items-center justify-center mr-3">
                                <i class="{{ $app['icon'] }}"></i>
                            </span>
                            <div>
                                <div class="font-bold text-gray-800">{{ $app['title'] }}</div>
                                <div class="text-[11px] text-gray-400">{{ $app['subtitle'] }}</div>
                            </div>
                        </a>
                    @endforeach
                    @if($currRole === 'Admin')
                        <div class="border-t border-pink-100 my-1"></div>
                        <div class="px-4 py-1.5 text-[11px] text-rose-800 bg-pink-50/80 flex items-center justify-between font-semibold">
                            <span><i class="fa-solid fa-shield-halved mr-1 text-rose-500"></i>Main Admin Full Access</span>
                            <span class="text-[9px] bg-pink-200/70 text-rose-900 px-1.5 py-0.5 rounded font-bold">Semua Modul</span>
                        </div>
                    @elseif($currRole === 'Owner')
                        <div class="border-t border-pink-100 my-1"></div>
                        <div class="px-4 py-1.5 text-[11px] text-fuchsia-900 bg-fuchsia-50/80 flex items-center justify-between font-semibold">
                            <span><i class="fa-solid fa-chart-pie mr-1 text-fuchsia-600"></i>Financial &amp; Profit Access</span>
                            <span class="text-[9px] bg-fuchsia-200/70 text-fuchsia-950 px-1.5 py-0.5 rounded font-bold">Keuangan &amp; Laba</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Breadcrumbs -->
            <div class="flex items-center text-sm space-x-2">
                <span class="text-rose-400">/</span>
                <span class="font-semibold tracking-tight text-rose-950">@yield('breadcrumb', 'PT Arunika Beauty Nusantara')</span>
            </div>
        </div>

        <div class="flex items-center space-x-3 text-xs">
            @if($originSuperUser)
                <form action="{{ route('return.super') }}" method="POST" id="return-super-header-form">
                    @csrf
                    <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white font-bold px-3 py-1.5 rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        <span>Kembali ke {{ $originSuperUser->role }}</span>
                    </button>
                </form>
            @endif

            <!-- Company Indicator -->
            <div class="hidden md:flex items-center space-x-1.5 bg-white/70 border border-pink-200/90 backdrop-blur-sm px-3 py-1.5 rounded-full shadow-sm">
                <i class="fa-solid fa-spa text-rose-500"></i>
                <span class="font-semibold text-rose-900">PT Arunika Beauty Nusantara</span>
            </div>

            <!-- Quick Side Drawer Button (Request Access / Master Admin Approvals) -->
            <button type="button" onclick="openAccessSideDrawer()" class="relative bg-white/75 hover:bg-white border border-pink-200/90 text-rose-900 px-3 py-1.5 rounded-lg shadow-sm transition flex items-center space-x-1.5 focus:outline-none" title="Cross-Division Access Panel">
                <i class="fa-solid {{ $currRole === 'Admin' ? 'fa-user-check text-emerald-600' : 'fa-key text-rose-500' }} text-xs"></i>
                <span class="hidden lg:inline font-semibold">{{ $currRole === 'Admin' ? 'Approvals' : (\App\Services\RoleAccessService::isSuperRole($currRole) ? 'Access Queue' : 'Request Access') }}</span>
                @if($pendingAccessReqCount > 0)
                    <span class="min-w-[18px] h-4 px-1 rounded-full {{ $currRole === 'Admin' ? 'bg-rose-500 text-white' : 'bg-amber-400 text-slate-900' }} text-[10px] font-extrabold flex items-center justify-center shadow-sm">{{ $pendingAccessReqCount }}</span>
                @endif
            </button>

            <!-- Notification Bell (team inbox) -->
            <div class="relative" id="notif-container">
                <button type="button" onclick="toggleNotif(event)" class="relative bg-white/75 hover:bg-white border border-pink-200/90 px-2.5 py-1.5 rounded-lg shadow-sm transition focus:outline-none" title="Notifikasi">
                    <i class="fa-solid fa-bell text-sm text-rose-700"></i>
                    @if($notifUnread > 0)
                        <span class="absolute -top-1.5 -right-1.5 min-w-[18px] h-4 px-1 rounded-full bg-rose-500 text-white ring-2 ring-white text-[10px] font-extrabold flex items-center justify-center shadow">{{ $notifUnread }}</span>
                    @endif
                </button>
                <div id="notif-menu" class="absolute right-0 top-full mt-1.5 w-96 bg-white text-gray-800 rounded-xl shadow-2xl hidden z-50 border border-pink-200 ring-1 ring-black/5">
                    <div class="px-4 py-2.5 border-b border-pink-100 bg-pink-50/60 rounded-t-xl flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-rose-700">Notifikasi Tim {{ $currRole }}</span>
                        @if($notifUnread > 0)
                            <form action="{{ route('notifications.read') }}" method="POST">
                                @csrf
                                <button type="submit" class="text-[11px] text-rose-600 hover:underline font-semibold">Tandai semua dibaca</button>
                            </form>
                        @endif
                    </div>
                    <div class="max-h-80 overflow-y-auto divide-y divide-pink-50">
                        @forelse($notifList as $n)
                            <a href="{{ $n->link ?: '#' }}" class="block px-4 py-2.5 hover:bg-pink-50/60 {{ $n->read_at ? '' : 'bg-pink-50/40' }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-800">{{ $n->title }}</span>
                                    @unless($n->read_at)<span class="w-2 h-2 rounded-full bg-rose-400"></span>@endunless
                                </div>
                                <div class="text-[11px] text-gray-600 mt-0.5">{{ $n->message }}</div>
                                <div class="text-[10px] text-gray-400 mt-1">{{ $n->created_at }}</div>
                            </a>
                        @empty
                            <div class="px-4 py-6 text-center text-xs text-gray-400 italic">Belum ada notifikasi.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Role / User Profile Menu (Standard Odoo 17 User Avatar & Profile Switcher) -->
            <div class="relative" id="user-profile-container">
                <button type="button" onclick="toggleUserProfile(event)" class="flex items-center space-x-2.5 bg-white/75 hover:bg-white border border-pink-200/90 px-2.5 py-1 rounded-lg shadow-sm transition focus:outline-none">
                    <div class="w-7 h-7 rounded-full {{ $activeProfile['avatar_color'] }} text-white font-bold flex items-center justify-center text-xs shadow-inner ring-2 ring-pink-200">
                        {{ substr($activeProfile['name'], 0, 1) }}
                    </div>
                    <div class="text-left hidden sm:block leading-tight">
                        <div class="font-bold text-xs text-rose-950">{{ $activeProfile['name'] }}</div>
                        <div class="text-[10px] text-rose-600">{{ $activeProfile['role_title'] }} ({{ $currRole }})</div>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] text-rose-500"></i>
                </button>

                <!-- User Dropdown Menu -->
                <div id="user-profile-menu" class="absolute right-0 top-full mt-1.5 w-80 bg-white text-gray-800 rounded-xl shadow-2xl py-2 hidden z-50 border border-rose-200 ring-1 ring-rose-900/5">
                    <!-- Current User Header -->
                    <div class="px-4 py-3 bg-gradient-to-r from-rose-50 to-pink-50 border-b border-rose-100 flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $activeProfile['avatar_color'] }} text-white font-bold flex items-center justify-center text-sm shadow">
                            {{ substr($activeProfile['name'], 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <div class="font-bold text-sm text-gray-900 leading-tight truncate">{{ $activeProfile['name'] }}</div>
                            <div class="text-xs text-rose-700 font-medium">{{ $activeProfile['email'] }}</div>
                            <div class="text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded mt-0.5 inline-block font-semibold">
                                Izin: {{ $activeProfile['access'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Role Switcher Section (Multi-role testing) -->
                    <div class="px-4 pt-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-rose-400 flex justify-between">
                        <span>Pilih Akun / Peran</span>
                    </div>

                    <form action="{{ route('switch.role') }}" method="POST" id="switch-role-form">
                        @csrf
                        <input type="hidden" name="role" id="switch-role-input" value="{{ $currRole }}">
                        <div class="px-2 space-y-1 max-h-72 overflow-y-auto">
                            @foreach($roleProfiles as $rKey => $pInfo)
                                <button type="button" onclick="selectRole('{{ $rKey }}')" class="w-full text-left px-2.5 py-1.5 rounded-md text-xs flex items-center justify-between transition {{ $currRole === $rKey ? 'bg-rose-50 text-rose-900 font-semibold border border-rose-200' : 'text-gray-700 hover:bg-rose-50/50' }}">
                                    <div class="flex items-center space-x-2.5 overflow-hidden">
                                        <span class="w-6 h-6 rounded-full {{ $pInfo['avatar_color'] }} text-white flex-shrink-0 flex items-center justify-center text-[10px] font-bold">
                                            {{ substr($pInfo['name'], 0, 1) }}
                                        </span>
                                        <div class="truncate">
                                            <div class="text-xs font-semibold leading-tight text-gray-800">{{ $pInfo['name'] }}</div>
                                            <div class="text-[10px] text-gray-500 leading-none">{{ $pInfo['email'] }} &bull; <strong class="text-rose-600">{{ $rKey }}</strong></div>
                                        </div>
                                    </div>
                                    @if($currRole === $rKey)
                                        <i class="fa-solid fa-check text-rose-600 text-xs ml-1 flex-shrink-0"></i>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </form>

                    <div class="border-t border-rose-100 my-2"></div>

                    <!-- Logout Button (Key Re-entry Enforcement) -->
                    <div class="px-2 pb-1 space-y-1">
                        @if($originSuperUser)
                            <form action="{{ route('return.super') }}" method="POST" id="return-super-menu-form">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-1.5 rounded-md text-xs text-rose-800 bg-rose-50 hover:bg-rose-100 flex items-center space-x-2 transition font-bold border border-rose-200">
                                    <i class="fa-solid fa-arrow-left text-xs"></i>
                                    <span>Kembali ke Sesi {{ $originSuperUser->role }} ({{ $originSuperUser->name }})</span>
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" id="logout-form" onsubmit="return requestLogoutConfirm(event, this, null)">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-1.5 rounded-md text-xs text-rose-600 hover:bg-rose-50 flex items-center space-x-2 transition font-medium border border-rose-200">
                                <i class="fa-solid fa-right-from-bracket text-xs"></i>
                                <span>Keluar / Log Out (Kunci Sesi)</span>
                            </button>
                        </form>
                    </div>

                    <!-- Reset Seed Shortcut (Restricted to Admin, Owner, or Finance) -->
                    @if(in_array($currRole, ['Admin', 'Owner', 'Finance']))
                        <div class="px-2 pb-1">
                            <form action="{{ route('reset.baseline') }}" method="POST" onsubmit="return confirm('Restore pristine seed baseline?');">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-1.5 rounded-md text-xs text-gray-500 hover:bg-gray-100 flex items-center space-x-2 transition">
                                    <i class="fa-solid fa-rotate-left text-xs"></i>
                                    <span>Reset Database to Baseline</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Account Switch / Logout Confirmation Modal -->
    <div id="logout-confirm-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-rose-950/35 backdrop-blur-[2px] px-4" onclick="if(event.target === this) closeLogoutConfirm()">
        <div class="bg-gradient-to-b from-[#FFF7FA] to-white rounded-2xl shadow-2xl border border-[#F2B6CE] w-full max-w-sm p-6 transform transition-all">
            <div class="flex items-start space-x-3.5">
                <div class="w-10 h-10 rounded-full bg-[#FADADD] border border-pink-200 text-rose-600 flex items-center justify-center flex-shrink-0 shadow-xs">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-rose-950 leading-snug">Are you sure you want to log out?</h3>
                    <p id="logout-confirm-subtitle" class="text-xs text-rose-700/80 mt-1 leading-relaxed">
                        Switching to another account will end your current session ({{ $activeProfile['name'] }} &bull; {{ $currRole }}).
                    </p>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end space-x-2.5">
                <button type="button" onclick="closeLogoutConfirm()" class="px-4 py-2 rounded-lg text-xs font-semibold text-rose-900 bg-[#FFF0F5] hover:bg-[#FADADD] border border-pink-200 transition focus:outline-none">
                    Cancel
                </button>
                <button type="button" onclick="confirmLogoutProceed()" class="px-4 py-2 rounded-lg text-xs font-bold text-white bg-gradient-to-r from-[#EC709F] to-[#D95386] hover:from-[#DF5D8E] hover:to-[#C74174] shadow-sm transition focus:outline-none">
                    Log Out
                </button>
            </div>
        </div>
    </div>

    <script>
        const CURRENT_ACTIVE_ROLE = @json($currRole);
        let pendingLogoutAction = null;

        function toggleAppSwitcher(e) {
            e.stopPropagation();
            const menu = document.getElementById('app-switcher-menu');
            const userMenu = document.getElementById('user-profile-menu');
            if (userMenu) userMenu.classList.add('hidden');
            menu.classList.toggle('hidden');
        }

        function toggleUserProfile(e) {
            e.stopPropagation();
            const userMenu = document.getElementById('user-profile-menu');
            const appMenu = document.getElementById('app-switcher-menu');
            if (appMenu) appMenu.classList.add('hidden');
            userMenu.classList.toggle('hidden');
        }

        function toggleNotif(e) {
            e.stopPropagation();
            const menu = document.getElementById('notif-menu');
            ['app-switcher-menu', 'user-profile-menu'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.add('hidden');
            });
            menu.classList.toggle('hidden');
        }

        function openLogoutConfirmModal(subtitleText, onConfirmFn) {
            pendingLogoutAction = onConfirmFn;
            const modal = document.getElementById('logout-confirm-modal');
            const subtitle = document.getElementById('logout-confirm-subtitle');
            const userMenu = document.getElementById('user-profile-menu');
            if (userMenu) userMenu.classList.add('hidden');
            if (subtitle && subtitleText) {
                subtitle.textContent = subtitleText;
            }
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeLogoutConfirm() {
            pendingLogoutAction = null;
            const modal = document.getElementById('logout-confirm-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function confirmLogoutProceed() {
            const fn = pendingLogoutAction;
            closeLogoutConfirm();
            if (typeof fn === 'function') {
                fn();
            }
        }

        function selectRole(role) {
            if (role === CURRENT_ACTIVE_ROLE) {
                const userMenu = document.getElementById('user-profile-menu');
                if (userMenu) userMenu.classList.add('hidden');
                return;
            }

            openLogoutConfirmModal(
                'You are leaving ' + CURRENT_ACTIVE_ROLE + ' to sign in to ' + role + '.',
                function () {
                    const input = document.getElementById('switch-role-input');
                    const form = document.getElementById('switch-role-form');
                    if (input && form) {
                        input.value = role;
                        form.submit();
                    }
                }
            );
        }

        function requestLogoutConfirm(e, formEl, targetRole) {
            if (formEl.dataset.confirmed === '1') {
                return true;
            }
            e.preventDefault();
            const msg = targetRole
                ? 'You are leaving ' + CURRENT_ACTIVE_ROLE + ' to return to ' + targetRole + '.'
                : 'You will be signed out of your current ' + CURRENT_ACTIVE_ROLE + ' session.';
            openLogoutConfirmModal(msg, function () {
                formEl.dataset.confirmed = '1';
                formEl.submit();
            });
            return false;
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLogoutConfirm();
                if (typeof closeAccessSideDrawer === 'function') {
                    closeAccessSideDrawer();
                }
            }
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            const appContainer = document.getElementById('app-switcher-container');
            const appMenu = document.getElementById('app-switcher-menu');
            if (appContainer && !appContainer.contains(e.target) && appMenu) {
                appMenu.classList.add('hidden');
            }

            const notifContainer = document.getElementById('notif-container');
            const notifMenu = document.getElementById('notif-menu');
            if (notifContainer && !notifContainer.contains(e.target) && notifMenu) {
                notifMenu.classList.add('hidden');
            }

            const userContainer = document.getElementById('user-profile-container');
            const userMenu = document.getElementById('user-profile-menu');
            if (userContainer && !userContainer.contains(e.target) && userMenu) {
                userMenu.classList.add('hidden');
            }
        });
    </script>

    <!-- Flash Alerts -->
    <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-14 pr-10 sm:pr-12 lg:pr-14 xl:pr-16 mt-3">
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-3 rounded shadow-sm text-sm flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-3 rounded shadow-sm text-sm flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-circle-xmark text-rose-500 text-lg"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
            </div>
        @endif
        @if(session('warning'))
            <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-3 rounded shadow-sm text-sm flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500 text-lg"></i>
                    <span>{{ session('warning') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-amber-600 hover:text-amber-900">&times;</button>
            </div>
        @endif
        @if(session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-800 p-3 rounded shadow-sm text-sm flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-circle-info text-blue-500 text-lg"></i>
                    <span>{{ session('info') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-blue-600 hover:text-blue-900">&times;</button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-14 pr-10 sm:pr-12 lg:pr-14 xl:pr-16 py-5">
        @yield('content')
    </main>

    <!-- Global Footer Tabs Component Helper -->
    <footer class="mt-auto bg-gradient-to-r from-[#FFF5F8] via-[#FADADD]/45 to-[#FFF5F8] border-t border-[#F2B6CE] py-3 px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-14 text-xs text-rose-800/80 flex flex-wrap gap-2 justify-between items-center">
        <div class="font-medium">PT Arunika Beauty Nusantara &copy; 2026 — NusaGlow ERP System</div>
        <div class="flex items-center space-x-4">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/85 text-rose-800 border border-pink-200 shadow-xs">
                <i class="fa-solid fa-spa mr-1 text-rose-400 text-[10px]"></i>Odoo 17.0 UI Pattern
            </span>
            <span>Active: <strong class="text-rose-950">{{ session('active_role', 'Sales') }}</strong></span>
        </div>
    </footer>

    @include('partials.access_side_drawer')

    @yield('scripts')
</body>
</html>
