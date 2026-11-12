@extends('layouts.odoo')

@section('title', 'S-01 Beranda dan App Switcher')
@section('breadcrumb', 'Apps > Beranda')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-[#FFF5F8] via-[#FADADD]/60 to-white rounded-xl shadow-sm border border-[#F2B6CE] p-6 flex flex-col md:flex-row justify-between items-start md:items-center">
        <div class="flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#F8C8DC] to-[#E879A6] text-white flex items-center justify-center shadow-sm flex-shrink-0">
                <i class="fa-solid fa-spa text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-rose-950">PT Arunika Beauty Nusantara</h1>
                <p class="text-xs text-rose-700/80 mt-1">NusaGlow ERP Web App &bull; Odoo 17.0 Desktop Architecture &bull; 7 Aplikasi Referensi &bull; 6 Peran Bisnis</p>
            </div>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <div class="bg-white/90 border border-[#F2B6CE] px-4 py-2 rounded-lg text-right shadow-sm">
                <div class="text-xs text-rose-500 font-medium">Bank Balance (1010)</div>
                <div class="text-lg font-bold text-rose-700">Rp{{ number_format($bankBalance, 0, ',', '.') }}</div>
            </div>
            <div class="bg-[#FADADD]/60 border border-[#F2B6CE] px-4 py-2 rounded-lg text-right">
                <div class="text-xs text-rose-600 font-medium">Active Role</div>
                <div class="text-lg font-bold text-rose-950">{{ session('active_role', 'Sales') }}</div>
            </div>
        </div>
    </div>

    <!-- Dynamic Division Applications Grid (Access Control) -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold uppercase text-rose-800/80 tracking-wider">
                Aplikasi Divisi ({{ count($accessibleApps) }} Aplikasi &bull; {{ session('active_role', 'Sales') }})
            </h2>
            @if(session('active_role') === 'Admin')
                <span class="text-xs bg-gradient-to-r from-[#FFF0F5] to-[#FADADD] text-rose-900 font-semibold px-3 py-0.5 rounded-full border border-[#F2B6CE] shadow-xs">
                    <i class="fa-solid fa-crown mr-1 text-rose-500"></i>Main Admin Full Access
                </span>
            @elseif(session('active_role') === 'Owner')
                <span class="text-xs bg-gradient-to-r from-fuchsia-50 to-[#FADADD] text-fuchsia-900 font-semibold px-3 py-0.5 rounded-full border border-fuchsia-200 shadow-xs">
                    <i class="fa-solid fa-chart-pie mr-1 text-fuchsia-600"></i>Executive Financial &amp; Profit Access
                </span>
            @else
                <span class="text-xs bg-[#FFF0F5] text-rose-800 px-3 py-0.5 rounded-full border border-pink-200 font-medium">
                    <i class="fa-solid fa-lock mr-1 text-rose-400"></i>Akses Terisolasi Divisi
                </span>
            @endif
        </div>
        @php
            $appGridCols = match(count($accessibleApps)) {
                7 => 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-7',
                6 => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-6',
                5 => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5',
                4 => 'grid-cols-2 sm:grid-cols-4',
                3 => 'grid-cols-1 sm:grid-cols-3',
                default => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
            };
        @endphp
        <div class="grid {{ $appGridCols }} gap-4">
            @foreach($accessibleApps as $appKey => $app)
                <a href="{{ route($app['route']) }}" class="bg-gradient-to-b from-white via-[#FFFBFD] to-[#FFF2F7] p-4 rounded-xl border border-[#F2C6D8] hover:from-[#FFF8FB] hover:to-[#FADADD]/60 hover:border-[#E879A6] hover:shadow-md transition text-center group flex flex-col items-center justify-center h-full">
                    <div class="w-12 h-12 mx-auto rounded-xl {{ $app['bg_color'] }} {{ $app['text_color'] }} border border-white/80 shadow-xs flex items-center justify-center text-xl mb-2.5 group-hover:scale-110 transition">
                        <i class="{{ $app['icon'] }}"></i>
                    </div>
                    <div class="font-bold text-rose-950 text-sm">{{ $app['title'] }}</div>
                    <div class="text-xs text-rose-600/75 mt-1 leading-snug">{{ $app['subtitle'] }}</div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Shortcut (Collapsible Accordion Tailored per Staff Division) -->
    <div class="bg-white rounded-xl border border-[#F2B6CE]/70 shadow-sm overflow-hidden" id="accordion-container">
        <button type="button" onclick="toggleScenarioMatrix()" class="w-full px-5 py-3.5 bg-[#FFF5F8] hover:bg-[#FDE8EF] transition flex items-center justify-between text-left focus:outline-none">
            <div class="flex items-center space-x-3">
                <span class="w-7 h-7 rounded-lg bg-[#FADADD] text-rose-700 flex items-center justify-center text-xs font-bold">
                    <i class="fa-solid fa-bolt"></i>
                </span>
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-rose-900">Shortcut Skenario ({{ count($accessibleShortcuts) }} Layar Terbuka)</h2>
                    <p class="text-[11px] text-rose-600/80">Tautan cepat yang disesuaikan hanya untuk kebutuhan operasional staf divisi {{ session('active_role', 'Sales') }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-[11px] bg-[#FADADD]/80 text-rose-900 font-semibold px-2 py-0.5 rounded-full border border-[#F2B6CE]">{{ count($accessibleShortcuts) }} Layar</span>
                <i id="accordion-icon" class="fa-solid fa-chevron-down text-rose-400 text-xs transition-transform duration-200"></i>
            </div>
        </button>

        <div id="scenario-matrix-content" class="hidden p-5 border-t border-pink-100 bg-white">
            <div class="grid gap-2.5 text-xs" style="grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));">
                @foreach($accessibleShortcuts as $sc)
                    <a href="{{ route($sc['route']) }}" class="p-2.5 rounded-lg bg-[#FFF8FA] border border-pink-100 hover:bg-[#FDE8EF] flex items-center justify-between transition">
                        <span><strong>{{ $sc['code'] }}</strong> {{ $sc['title'] }}</span>
                        <i class="fa-solid fa-arrow-right text-[10px] text-rose-400"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if(!empty($adminStaffMatrix))
        <!-- Main Admin: Staff Access Matrix & Permission Locator (Point 4) -->
        <div class="bg-gradient-to-br from-[#FFF6F9] via-[#FADADD] to-[#F4B8CE] rounded-xl p-5 text-rose-950 shadow-md border border-[#EDA4C0]" id="admin-staff-matrix">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b border-rose-300/60">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="w-7 h-7 rounded-lg bg-white/85 border border-pink-200 text-rose-600 flex items-center justify-center text-xs shadow-sm">
                            <i class="fa-solid fa-users-gear"></i>
                        </span>
                        <h3 class="font-bold text-sm tracking-wide text-rose-950">Main Admin: Matriks & Inspeksi Hak Akses Staf Divisi</h3>
                    </div>
                    <p class="text-xs text-rose-800/85 mt-1">
                        Sebagai Main Super Admin, Anda memiliki akses penuh ke seluruh modul dan dapat memantau atau menginspeksi alur kerja staf divisi secara spesifik.
                    </p>
                </div>
                <div class="mt-3 md:mt-0 text-xs bg-white/85 px-3 py-1.5 rounded-lg border border-pink-200 text-rose-900 font-semibold shadow-sm">
                    <i class="fa-solid fa-shield-check mr-1 text-emerald-600"></i>Superuser Mode Active
                </div>
            </div>

            <div class="grid gap-3.5" style="grid-template-columns: repeat(auto-fit, minmax(275px, 1fr));">
                @foreach($adminStaffMatrix as $sKey => $staff)
                    <div class="bg-white/80 hover:bg-white/95 backdrop-blur-sm rounded-xl p-4 border border-pink-200/90 shadow-sm transition flex flex-col justify-between h-full">
                        <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="w-7 h-7 rounded-full {{ $staff['avatar_color'] }} text-white text-xs font-bold flex items-center justify-center shadow-sm">
                                    {{ substr($staff['name'], 0, 1) }}
                                </span>
                                <div>
                                    <div class="text-xs font-bold text-rose-950 leading-tight">{{ $staff['name'] }}</div>
                                    <div class="text-[10px] text-rose-600 font-mono">{{ $staff['email'] }}</div>
                                </div>
                            </div>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $staff['badge_color'] }}">
                                {{ $staff['role'] }}
                            </span>
                        </div>
                        <div class="text-[11px] text-rose-900 mb-2">
                            <span class="text-rose-500 block text-[10px] font-medium">Divisi & Jabatan:</span>
                            {{ $staff['dept'] }} &bull; {{ $staff['role_title'] }}
                        </div>
                        <div class="bg-[#FFF2F7] border border-pink-200/70 p-2.5 rounded-lg text-[10px] space-y-1 mb-3">
                            <div class="flex justify-between text-rose-800">
                                <span class="text-rose-600">Aplikasi Diizinkan:</span>
                                <strong class="text-rose-950">{{ $staff['allowed_apps_count'] }} Apps
                                    @if(!empty($staff['granted_app_keys']))
                                        <span class="text-emerald-700">(+{{ count($staff['granted_app_keys']) }} tambahan)</span>
                                    @endif
                                </strong>
                            </div>
                            <div class="flex justify-between text-rose-800">
                                <span class="text-rose-600">Shortcut Layar:</span>
                                <strong class="text-rose-950">{{ $staff['allowed_shortcuts_count'] }} Layar</strong>
                            </div>
                            @if(!empty($staff['grants']) && count($staff['grants']) > 0)
                                <div class="pt-1.5 mt-1.5 border-t border-pink-200/80 space-y-1">
                                    <div class="text-[9px] uppercase tracking-wider text-rose-700 font-bold">Akses Sementara (Klik Hapus untuk Cabut):</div>
                                    @foreach($staff['grants'] as $sGrant)
                                        <div class="flex items-center justify-between bg-white border border-pink-200 rounded px-2 py-1">
                                            <div class="truncate pr-2">
                                                <span class="text-emerald-700 font-bold">{{ $allApps[$sGrant->app_key]['title'] ?? $sGrant->app_key }}</span>
                                                @if($sGrant->expires_at)
                                                    <span class="text-[9px] text-rose-500 block">s/d {{ $sGrant->expires_at->format('d M H:i') }}</span>
                                                @endif
                                            </div>
                                            <form action="{{ route('access.revoke', $sGrant->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white text-[10px] font-bold px-2 py-0.5 rounded transition flex items-center space-x-1">
                                                    <i class="fa-solid fa-trash-can text-[9px]"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        </div>
                        <div class="flex justify-end mt-auto pt-1">
                            @if($sKey === $user->role)
                                <span class="w-full text-center text-xs bg-emerald-50 border border-emerald-300 text-emerald-800 font-semibold py-1.5 px-3 rounded-lg flex items-center justify-center space-x-1.5">
                                    <i class="fa-solid fa-circle-check text-[10px] text-emerald-600"></i>
                                    <span>Akun Aktif Saat Ini</span>
                                </span>
                            @else
                                <button type="button" onclick="inspectStaffRole('{{ $sKey }}')" class="w-full text-center text-xs bg-gradient-to-r from-[#EC709F] to-[#D95386] hover:from-[#DF5D8E] hover:to-[#C74174] text-white font-semibold py-1.5 px-3 rounded-lg shadow-sm transition flex items-center justify-center space-x-1.5">
                                    <i class="fa-solid fa-arrow-right-to-bracket text-[10px]"></i>
                                    <span>Inspeksi Akses {{ $staff['role'] }}</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <form action="{{ route('switch.role') }}" method="POST" id="inspect-role-form" class="hidden">
                @csrf
                <input type="hidden" name="inspect" value="1">
                <input type="hidden" name="role" id="inspect-role-input" value="">
            </form>

            <!-- Cross-division access management -->
            <div class="mt-5 pt-4 border-t border-rose-300/60">
                <div class="flex items-center space-x-2 mb-1">
                    <i class="fa-solid fa-key text-rose-600 text-xs"></i>
                    <h4 class="font-bold text-sm tracking-wide text-rose-950">Beri Akses Lintas Divisi</h4>
                </div>
                <p class="text-xs text-rose-800/85 mb-3">
                    Berikan tim divisi akses sementara ke modul divisi lain saat dibutuhkan. Tim penerima dan divisi pemilik modul otomatis mendapat notifikasi (ikon lonceng di pojok kanan atas).
                </p>

                @if($errors->any())
                    <div class="mb-3 text-xs bg-rose-100 border border-rose-300 text-rose-800 rounded-lg p-2">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('access.grant') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-4 gap-3 text-xs">
                    @csrf
                    <div>
                        <label class="block text-rose-900 mb-1 font-semibold">Tim Penerima</label>
                        <select name="grantee_role" required class="w-full bg-white/95 border border-pink-300 rounded-lg p-2 text-rose-950 focus:border-rose-400 focus:outline-none shadow-sm">
                            @foreach($adminStaffMatrix as $sKey => $staff)
                                @if(!$staff['is_super'])
                                    <option value="{{ $sKey }}">{{ $sKey }} — {{ $staff['name'] }}</option>
                                @endif
                            @endforeach
                        </select>
                        <label class="block text-rose-900 mt-2.5 mb-1 font-semibold">Durasi Akses Sementara</label>
                        <select name="duration_hours" class="w-full bg-white/95 border border-pink-300 rounded-lg p-2 text-rose-950 focus:border-rose-400 focus:outline-none shadow-sm">
                            <option value="1">1 Jam (Otomatis Berakhir)</option>
                            <option value="4">4 Jam (Otomatis Berakhir)</option>
                            <option value="8">8 Jam / 1 Shift</option>
                            <option value="24" selected>24 Jam / 1 Hari</option>
                            <option value="72">3 Hari</option>
                            <option value="168">7 Hari</option>
                            <option value="">Sampai Dihapus Manual</option>
                        </select>
                        <label class="block text-rose-900 mt-2.5 mb-1 font-semibold">Catatan (opsional)</label>
                        <input type="text" name="note" maxlength="255" placeholder="Contoh: bantu rekonsiliasi akhir bulan" class="w-full bg-white/95 border border-pink-300 rounded-lg p-2 text-rose-950 placeholder-rose-400 focus:border-rose-400 focus:outline-none shadow-sm">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-rose-900 mb-1 font-semibold">Modul yang Diberikan</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                            @foreach($allApps as $aKey => $aInfo)
                                <label class="flex items-center space-x-2 bg-white/80 hover:bg-white rounded-lg px-2.5 py-1.5 cursor-pointer border border-pink-200/90 text-rose-950 shadow-sm transition">
                                    <input type="checkbox" name="apps[]" value="{{ $aKey }}" class="rounded border-pink-300 accent-rose-500">
                                    <span class="font-medium">{{ $aInfo['title'] }} <span class="text-rose-500 font-normal">({{ $aInfo['division'] }})</span></span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-rose-700/80 mt-1.5">Modul bawaan tim penerima akan dilewati otomatis. Akses akan otomatis berakhir sesuai durasi atau dapat dihapus kapan saja di bawah.</p>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-2.5 px-3 rounded-lg shadow-sm transition flex items-center justify-center space-x-1.5">
                            <i class="fa-solid fa-paper-plane text-[10px]"></i>
                            <span>Beri Akses & Kirim Notifikasi</span>
                        </button>
                    </div>
                </form>

                <!-- Active Allocated Access List + Delete Access Controls -->
                <div class="mt-5 pt-4 border-t border-rose-300/60">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-rose-900 flex items-center space-x-1.5">
                                <i class="fa-solid fa-shield-halved text-rose-600"></i>
                                <span>Alokasi Akses Tambahan Aktif ({{ count($accessGrants) }})</span>
                            </div>
                            <p class="text-[11px] text-rose-800/80">Hapus alokasi akses di bawah ini kapan saja agar akses lintas divisi tidak berlangsung selamanya.</p>
                        </div>
                        @if(count($accessGrants) > 0)
                            <form action="{{ route('access.revoke.all') }}" method="POST" onsubmit="return confirm('Hapus seluruh alokasi akses lintas divisi yang sedang aktif?');">
                                @csrf
                                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition flex items-center space-x-1.5 shadow-sm">
                                    <i class="fa-solid fa-trash-can text-[10px]"></i>
                                    <span>Hapus Semua Akses ({{ count($accessGrants) }})</span>
                                </button>
                            </form>
                        @endif
                    </div>

                    @forelse($accessGrants as $g)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-white/85 hover:bg-white border border-pink-200 rounded-xl px-3.5 py-2.5 mb-2 text-xs shadow-sm transition">
                            <div class="space-y-0.5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded bg-pink-100 border border-pink-200 text-rose-900 font-bold">{{ $g->grantee_role }}</span>
                                    <i class="fa-solid fa-arrow-right text-[10px] text-rose-400"></i>
                                    <strong class="text-emerald-700 text-sm">{{ $allApps[$g->app_key]['title'] ?? $g->app_key }}</strong>
                                    <span class="text-rose-500">({{ $allApps[$g->app_key]['division'] ?? '-' }})</span>
                                    @if($g->expires_at)
                                        <span class="px-2 py-0.5 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-[10px] font-semibold">
                                            <i class="fa-regular fa-clock mr-1"></i>Berakhir {{ $g->expires_at->diffForHumans() }} ({{ $g->expires_at->format('d M Y H:i') }})
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-pink-50 border border-pink-200 text-rose-700 text-[10px]">
                                            Aktif sampai dihapus
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-rose-700/80">
                                    Diberikan oleh <strong class="text-rose-900">{{ $g->granted_by }}</strong> pada {{ $g->created_at }}
                                    @if($g->note) &bull; <span class="italic text-rose-800">“{{ $g->note }}”</span>@endif
                                </div>
                            </div>
                            <form action="{{ route('access.revoke', $g->id) }}" method="POST" class="flex-shrink-0">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto bg-rose-500 hover:bg-rose-600 text-white font-bold px-3.5 py-1.5 rounded-lg shadow-sm transition flex items-center justify-center space-x-1.5">
                                    <i class="fa-solid fa-trash-can text-[11px]"></i>
                                    <span>Hapus Akses</span>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-xs text-rose-700/80 bg-white/70 border border-pink-200 rounded-xl p-3 italic text-center">
                            Belum ada alokasi akses lintas divisi yang sedang aktif.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    @if(!empty($showFinanceChart) || !empty($showInventoryChart))
    <!-- Role-Allocated Business Analytics & Inventory Overview -->
    <div class="grid grid-cols-1 {{ (!empty($showFinanceChart) && !empty($showInventoryChart)) ? 'lg:grid-cols-3' : '' }} gap-6">
        @if(!empty($showFinanceChart))
        <!-- Chart 1: Financial & Asset Structure (Admin, Owner, Finance) -->
        <div class="bg-white rounded-xl border border-[#F2C6D8] overflow-hidden shadow-sm">
            <div class="bg-gradient-to-r from-[#FFF2F7] via-[#FADADD]/60 to-white px-5 py-3.5 border-b border-[#F2C6D8] flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-rose-950 text-sm">Struktur Likuiditas &amp; Aset</h3>
                    <p class="text-[11px] text-rose-700/75">Komposisi Kas, Stok, Piutang &amp; Utang</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800">
                    <i class="fa-solid fa-circle-check text-[10px] mr-1 text-emerald-600"></i>Live Ledger
                </span>
            </div>
            <div class="p-5 {{ empty($showInventoryChart) ? 'grid grid-cols-1 md:grid-cols-2 gap-6 items-center' : '' }}">
                <div class="relative h-60 xl:h-72 flex items-center justify-center">
                    <canvas id="financeChart"></canvas>
                </div>
                <div class="{{ empty($showInventoryChart) ? '' : 'mt-4 pt-3 border-t border-pink-100' }} grid grid-cols-2 gap-2.5 text-xs">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-[#FFF2F7] to-[#FADADD]/55 border border-pink-200">
                        <span class="text-rose-600 block text-[10px] font-medium">Kas Bank (1010)</span>
                        <strong class="text-rose-950 text-sm">Rp{{ number_format($bankBalance, 0, ',', '.') }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50/70 border border-amber-200/80">
                        <span class="text-amber-700 block text-[10px] font-medium">Valuasi Persediaan (1200)</span>
                        <strong class="text-amber-950 text-sm">Rp{{ number_format($stockValuation, 0, ',', '.') }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-gradient-to-br from-sky-50 to-indigo-50/60 border border-sky-200/80">
                        <span class="text-sky-700 block text-[10px] font-medium">Piutang Usaha (1100)</span>
                        <strong class="text-sky-950 text-sm">Rp{{ number_format($totalAr, 0, ',', '.') }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-gradient-to-br from-rose-50 to-pink-100/60 border border-rose-200">
                        <span class="text-rose-600 block text-[10px] font-medium">Utang Dagang (2100)</span>
                        <strong class="text-rose-900 text-sm">Rp{{ number_format($totalAp, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(!empty($showInventoryChart))
        <!-- Chart 2: Product Category Inventory Distribution (Sales, Warehouse) -->
        <div class="bg-white rounded-xl border border-[#F2C6D8] overflow-hidden shadow-sm {{ !empty($showFinanceChart) ? 'lg:col-span-2' : '' }}">
            <div class="bg-gradient-to-r from-[#FFF2F7] via-[#FADADD]/60 to-white px-5 py-3.5 border-b border-[#F2C6D8] flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="font-bold text-rose-950 text-sm">Distribusi Ketersediaan Persediaan Produk</h3>
                    <p class="text-[11px] text-rose-700/75">Unit fisik on hand per kategori & perbandingan antar-gudang</p>
                </div>
                <div class="flex items-center space-x-2 text-xs">
                    <span class="px-2.5 py-1 rounded-full bg-white/90 border border-pink-200 font-medium text-rose-900 shadow-xs">
                        <i class="fa-solid fa-warehouse mr-1 text-amber-500"></i>WH-JKT: <strong>{{ $totalUnitsJkt }}</strong> unit
                    </span>
                    <span class="px-2.5 py-1 rounded-full bg-white/90 border border-pink-200 font-medium text-rose-900 shadow-xs">
                        <i class="fa-solid fa-store mr-1 text-rose-500"></i>ST-BDG: <strong>{{ $totalUnitsBdg }}</strong> unit
                    </span>
                </div>
            </div>
            <div class="p-5">
                <div class="relative h-64 xl:h-80">
                    <canvas id="inventoryChart"></canvas>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Chatter for S-01 -->
    @include('partials.odoo_chatter', ['auditLogs' => $recentAudit])
</div>
@endsection

@section('scripts')
@if(!empty($showFinanceChart) || !empty($showInventoryChart))
<!-- Chart.js for Interactive Visual Dashboard -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endif
<script>
    function inspectStaffRole(role) {
        const input = document.getElementById('inspect-role-input');
        const form = document.getElementById('inspect-role-form');
        if (input && form) {
            input.value = role;
            form.submit();
        }
    }

    function toggleScenarioMatrix() {
        const content = document.getElementById('scenario-matrix-content');
        const icon = document.getElementById('accordion-icon');
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            content.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if(!empty($showFinanceChart))
        const financeCanvas = document.getElementById('financeChart');
        if (financeCanvas) {
            new Chart(financeCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Kas Bank (1010)', 'Valuasi Persediaan (1200)', 'Piutang Usaha (1100)', 'Utang Dagang (2100)'],
                    datasets: [{
                        data: [
                            {{ (float)$bankBalance }},
                            {{ (float)$stockValuation }},
                            {{ (float)$totalAr }},
                            {{ (float)$totalAp }}
                        ],
                        backgroundColor: ['#EC709F', '#F6AD55', '#7F9CF5', '#F43F5E'],
                        borderWidth: 2,
                        borderColor: '#FFF9FB'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                font: { size: 10 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let val = context.parsed || 0;
                                    return ' ' + context.label + ': Rp' + val.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }
        @endif

        @if(!empty($showInventoryChart))
        const invCanvas = document.getElementById('inventoryChart');
        if (invCanvas) {
            new Chart(invCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Cleanser', 'Toner', 'Serum', 'Masker', 'Lipstick'],
                    datasets: [{
                        label: 'Unit On Hand',
                        data: [
                            {{ $catDistribution['Cleanser'] ?? 0 }},
                            {{ $catDistribution['Toner'] ?? 0 }},
                            {{ $catDistribution['Serum'] ?? 0 }},
                            {{ $catDistribution['Mask'] ?? 0 }},
                            {{ $catDistribution['Lipstick'] ?? 0 }}
                        ],
                        backgroundColor: [
                            'rgba(236, 112, 159, 0.85)',
                            'rgba(244, 143, 177, 0.85)',
                            'rgba(192, 132, 252, 0.82)',
                            'rgba(251, 191, 36, 0.82)',
                            'rgba(244, 63, 94, 0.82)'
                        ],
                        borderRadius: 8,
                        maxBarThickness: 45
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 20, font: { size: 10 } },
                            grid: { color: 'rgba(0, 0, 0, 0.05)' }
                        },
                        x: {
                            ticks: { font: { size: 11, weight: 'bold' } },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.parsed.y + ' unit fisik';
                                }
                            }
                        }
                    }
                }
            });
        }
        @endif
    });
</script>
@endsection
