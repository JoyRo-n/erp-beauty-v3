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
            --odoo-primary: #714B67;
            --odoo-dark: #5A3C52;
            --odoo-accent: #008784;
            --odoo-bg: #F8F9FA;
            --odoo-border: #E2E8F0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--odoo-bg);
            color: #2D3748;
        }
        .odoo-header {
            background-color: var(--odoo-primary);
        }
        .odoo-btn-primary {
            background-color: var(--odoo-primary);
            color: white;
        }
        .odoo-btn-primary:hover {
            background-color: var(--odoo-dark);
        }
        .odoo-btn-secondary {
            background-color: #E2E8F0;
            color: #4A5568;
        }
        .odoo-btn-secondary:hover {
            background-color: #CBD5E0;
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
    <header class="odoo-header text-white h-12 flex items-center justify-between px-4 shadow select-none relative z-50">
        <div class="flex items-center space-x-4">
            <!-- App Switcher Dropdown (▦ Icon) - Dynamic by Division / Role -->
            <div class="relative" id="app-switcher-container">
                <button type="button" id="app-switcher-btn" onclick="toggleAppSwitcher(event)" class="flex items-center space-x-2 text-lg hover:bg-white/20 px-2.5 py-1.5 rounded transition focus:outline-none">
                    <i class="fa-solid fa-table-cells text-xl"></i>
                    <span class="font-bold text-sm tracking-wide">Apps</span>
                    <i class="fa-solid fa-chevron-down text-xs opacity-75"></i>
                </button>
                <div id="app-switcher-menu" class="absolute left-0 top-full mt-1 w-72 bg-white text-gray-800 rounded-lg shadow-2xl py-2 hidden z-50 border border-gray-200 ring-1 ring-black/5">
                    <div class="px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-gray-400 border-b pb-2 mb-1 flex justify-between items-center">
                        <span>Aplikasi Divisi &bull; {{ $currRole }}</span>
                        <a href="{{ route('s01.home') }}" class="text-purple-600 hover:underline normal-case font-semibold"><i class="fa-solid fa-house mr-1"></i>Home</a>
                    </div>
                    @foreach($accessibleApps as $app)
                        <a href="{{ route($app['route']) }}" class="flex items-center px-4 py-2 hover:bg-purple-50 text-sm font-medium transition">
                            <span class="w-8 h-8 rounded {{ $app['bg_color'] }} {{ $app['text_color'] }} flex items-center justify-center mr-3">
                                <i class="{{ $app['icon'] }}"></i>
                            </span>
                            <div>
                                <div class="font-bold text-gray-800">{{ $app['title'] }}</div>
                                <div class="text-[11px] text-gray-400">{{ $app['subtitle'] }}</div>
                            </div>
                        </a>
                    @endforeach
                    @if(in_array($currRole, ['Admin', 'Owner']))
                        <div class="border-t my-1"></div>
                        <div class="px-4 py-1.5 text-[11px] text-purple-800 bg-purple-50 flex items-center justify-between font-semibold">
                            <span><i class="fa-solid fa-shield-halved mr-1 text-purple-600"></i>Main Admin Full Access</span>
                            <span class="text-[9px] bg-purple-200 text-purple-900 px-1.5 py-0.5 rounded font-bold">Semua Modul</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Breadcrumbs -->
            <div class="flex items-center text-sm space-x-2">
                <span class="opacity-60">/</span>
                <span class="font-medium">@yield('breadcrumb', 'PT Arunika Beauty Nusantara')</span>
            </div>
        </div>

        <div class="flex items-center space-x-4 text-xs">
            @if($originSuperUser)
                <form action="{{ route('return.super') }}" method="POST" id="return-super-header-form">
                    @csrf
                    <button type="submit" class="bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold px-3 py-1 rounded shadow-sm transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        <span>Kembali ke {{ $originSuperUser->role }}</span>
                    </button>
                </form>
            @endif

            <!-- Company Indicator -->
            <div class="hidden md:flex items-center space-x-1.5 bg-black/20 px-2.5 py-1 rounded">
                <i class="fa-solid fa-building text-pink-300"></i>
                <span>PT Arunika Beauty Nusantara</span>
            </div>

            <!-- Quick Side Drawer Button (Request Access / Master Admin Approvals) -->
            <button type="button" onclick="openAccessSideDrawer()" class="relative bg-white/15 hover:bg-white/25 px-2.5 py-1 rounded transition flex items-center space-x-1.5 focus:outline-none" title="Cross-Division Access Panel">
                <i class="fa-solid {{ $currRole === 'Admin' ? 'fa-user-check text-emerald-300' : 'fa-key text-amber-300' }} text-xs"></i>
                <span class="hidden lg:inline font-semibold">{{ $currRole === 'Admin' ? 'Approvals' : (\App\Services\RoleAccessService::isSuperRole($currRole) ? 'Access Queue' : 'Request Access') }}</span>
                @if($pendingAccessReqCount > 0)
                    <span class="min-w-[16px] h-4 px-1 rounded-full {{ $currRole === 'Admin' ? 'bg-rose-500' : 'bg-amber-500' }} text-white text-[10px] font-bold flex items-center justify-center">{{ $pendingAccessReqCount }}</span>
                @endif
            </button>

            <!-- Notification Bell (team inbox) -->
            <div class="relative" id="notif-container">
                <button type="button" onclick="toggleNotif(event)" class="relative hover:bg-white/10 px-2.5 py-1.5 rounded transition focus:outline-none" title="Notifikasi">
                    <i class="fa-solid fa-bell text-base"></i>
                    @if($notifUnread > 0)
                        <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center">{{ $notifUnread }}</span>
                    @endif
                </button>
                <div id="notif-menu" class="absolute right-0 top-full mt-1.5 w-96 bg-white text-gray-800 rounded-lg shadow-2xl hidden z-50 border border-gray-200 ring-1 ring-black/5">
                    <div class="px-4 py-2.5 border-b flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Notifikasi Tim {{ $currRole }}</span>
                        @if($notifUnread > 0)
                            <form action="{{ route('notifications.read') }}" method="POST">
                                @csrf
                                <button type="submit" class="text-[11px] text-purple-600 hover:underline font-semibold">Tandai semua dibaca</button>
                            </form>
                        @endif
                    </div>
                    <div class="max-h-80 overflow-y-auto divide-y">
                        @forelse($notifList as $n)
                            <a href="{{ $n->link ?: '#' }}" class="block px-4 py-2.5 hover:bg-purple-50 {{ $n->read_at ? '' : 'bg-purple-50/60' }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-800">{{ $n->title }}</span>
                                    @unless($n->read_at)<span class="w-2 h-2 rounded-full bg-rose-500"></span>@endunless
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
                <button type="button" onclick="toggleUserProfile(event)" class="flex items-center space-x-2.5 hover:bg-white/10 px-2.5 py-1 rounded transition focus:outline-none">
                    <div class="w-7 h-7 rounded-full {{ $activeProfile['avatar_color'] }} text-white font-bold flex items-center justify-center text-xs shadow-inner ring-2 ring-white/30">
                        {{ substr($activeProfile['name'], 0, 1) }}
                    </div>
                    <div class="text-left hidden sm:block leading-tight">
                        <div class="font-bold text-xs text-white">{{ $activeProfile['name'] }}</div>
                        <div class="text-[10px] text-purple-200 opacity-90">{{ $activeProfile['role_title'] }} ({{ $currRole }})</div>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] opacity-75"></i>
                </button>

                <!-- User Dropdown Menu -->
                <div id="user-profile-menu" class="absolute right-0 top-full mt-1.5 w-80 bg-white text-gray-800 rounded-lg shadow-2xl py-2 hidden z-50 border border-gray-200 ring-1 ring-black/5">
                    <!-- Current User Header -->
                    <div class="px-4 py-3 bg-purple-50/60 border-b border-gray-100 flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $activeProfile['avatar_color'] }} text-white font-bold flex items-center justify-center text-sm shadow">
                            {{ substr($activeProfile['name'], 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <div class="font-bold text-sm text-gray-900 leading-tight truncate">{{ $activeProfile['name'] }}</div>
                            <div class="text-xs text-purple-700 font-medium">{{ $activeProfile['email'] }}</div>
                            <div class="text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded mt-0.5 inline-block font-semibold">
                                Izin: {{ $activeProfile['access'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Role Switcher Section (Multi-role testing) -->
                    <div class="px-4 pt-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-gray-400 flex justify-between">
                        <span>Pilih Akun / Peran</span>
                    </div>

                    <form action="{{ route('switch.role') }}" method="POST" id="switch-role-form">
                        @csrf
                        <input type="hidden" name="role" id="switch-role-input" value="{{ $currRole }}">
                        <div class="px-2 space-y-1 max-h-72 overflow-y-auto">
                            @foreach($roleProfiles as $rKey => $pInfo)
                                <button type="button" onclick="selectRole('{{ $rKey }}')" class="w-full text-left px-2.5 py-1.5 rounded-md text-xs flex items-center justify-between transition {{ $currRole === $rKey ? 'bg-purple-100/80 text-purple-900 font-semibold border border-purple-200' : 'text-gray-700 hover:bg-gray-100' }}">
                                    <div class="flex items-center space-x-2.5 overflow-hidden">
                                        <span class="w-6 h-6 rounded-full {{ $pInfo['avatar_color'] }} text-white flex-shrink-0 flex items-center justify-center text-[10px] font-bold">
                                            {{ substr($pInfo['name'], 0, 1) }}
                                        </span>
                                        <div class="truncate">
                                            <div class="text-xs font-semibold leading-tight text-gray-800">{{ $pInfo['name'] }}</div>
                                            <div class="text-[10px] text-gray-500 leading-none">{{ $pInfo['email'] }} &bull; <strong class="text-purple-700">{{ $rKey }}</strong></div>
                                        </div>
                                    </div>
                                    @if($currRole === $rKey)
                                        <i class="fa-solid fa-check text-purple-600 text-xs ml-1 flex-shrink-0"></i>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </form>

                    <div class="border-t border-gray-100 my-2"></div>

                    <!-- Logout Button (Key Re-entry Enforcement) -->
                    <div class="px-2 pb-1 space-y-1">
                        @if($originSuperUser)
                            <form action="{{ route('return.super') }}" method="POST" id="return-super-menu-form">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-1.5 rounded-md text-xs text-purple-800 bg-purple-50 hover:bg-purple-100 flex items-center space-x-2 transition font-bold border border-purple-200">
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
    <div id="logout-confirm-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-[1px] px-4" onclick="if(event.target === this) closeLogoutConfirm()">
        <div class="bg-white rounded-xl shadow-2xl border border-gray-200 w-full max-w-sm p-6 transform transition-all">
            <div class="flex items-start space-x-3.5">
                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 leading-snug">Are you sure you want to log out?</h3>
                    <p id="logout-confirm-subtitle" class="text-xs text-gray-500 mt-1 leading-relaxed">
                        Switching to another account will end your current session ({{ $activeProfile['name'] }} &bull; {{ $currRole }}).
                    </p>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end space-x-2.5">
                <button type="button" onclick="closeLogoutConfirm()" class="px-4 py-2 rounded-lg text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-200 transition focus:outline-none">
                    Cancel
                </button>
                <button type="button" onclick="confirmLogoutProceed()" class="px-4 py-2 rounded-lg text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition focus:outline-none">
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
    <div class="max-w-7xl mx-auto w-full px-4 mt-3">
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
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 py-4">
        @yield('content')
    </main>

    <!-- Global Footer Tabs Component Helper -->
    <footer class="mt-auto bg-white border-t border-gray-200 py-3 px-6 text-xs text-gray-500 flex justify-between items-center">
        <div>PT Arunika Beauty Nusantara &copy; 2026 — NusaGlow ERP System</div>
        <div class="flex items-center space-x-4">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                Odoo 17.0 UI Pattern
            </span>
            <span>Active: <strong>{{ session('active_role', 'Sales') }}</strong></span>
        </div>
    </footer>

    @include('partials.access_side_drawer')

    @yield('scripts')
</body>
</html>
