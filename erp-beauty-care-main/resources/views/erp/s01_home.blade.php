@extends('layouts.odoo')

@section('title', 'S-01 Beranda dan App Switcher')
@section('breadcrumb', 'Apps > Beranda')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">PT Arunika Beauty Nusantara</h1>
            <p class="text-xs text-gray-500 mt-1">NusaGlow ERP Web App &bull; Odoo 17.0 Desktop Architecture &bull; 6 Business Roles</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <div class="bg-purple-50 border border-purple-200 px-4 py-2 rounded-lg text-right">
                <div class="text-xs text-gray-500">Bank Balance (1010)</div>
                <div class="text-lg font-bold text-purple-700">Rp{{ number_format($bankBalance, 0, ',', '.') }}</div>
            </div>
            <div class="bg-gray-50 border border-gray-200 px-4 py-2 rounded-lg text-right">
                <div class="text-xs text-gray-500">Active Role</div>
                <div class="text-lg font-bold text-gray-800">{{ session('active_role', 'Sales') }}</div>
            </div>
        </div>
    </div>

    <!-- Dynamic Division Applications Grid (SR-19 Access Control) -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold uppercase text-gray-500 tracking-wider">
                Aplikasi Divisi ({{ count($accessibleApps) }} Aplikasi &bull; {{ session('active_role', 'Sales') }})
            </h2>
            @if(in_array(session('active_role'), ['Admin', 'Owner']))
                <span class="text-xs bg-purple-100 text-purple-800 font-semibold px-2.5 py-0.5 rounded-full border border-purple-200">
                    <i class="fa-solid fa-crown mr-1 text-purple-600"></i>Main Admin Full Access
                </span>
            @else
                <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full">
                    <i class="fa-solid fa-lock mr-1 text-gray-400"></i>Akses Terisolasi Divisi
                </span>
            @endif
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-{{ min(count($accessibleApps), 6) }} gap-4">
            @foreach($accessibleApps as $appKey => $app)
                <a href="{{ route($app['route']) }}" class="bg-white p-4 rounded-xl border border-gray-200 {{ $app['hover_border'] }} hover:shadow-md transition text-center group">
                    <div class="w-12 h-12 mx-auto rounded-lg {{ $app['bg_color'] }} {{ $app['text_color'] }} flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">
                        <i class="{{ $app['icon'] }}"></i>
                    </div>
                    <div class="font-bold text-gray-800 text-sm">{{ $app['title'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $app['subtitle'] }}</div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Shortcut (Collapsible Accordion Tailored per Staff Division) -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" id="accordion-container">
        <button type="button" onclick="toggleScenarioMatrix()" class="w-full px-5 py-3.5 bg-gray-50/80 hover:bg-gray-100 transition flex items-center justify-between text-left focus:outline-none">
            <div class="flex items-center space-x-3">
                <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">
                    <i class="fa-solid fa-bolt"></i>
                </span>
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700">Shortcut Skenario ({{ count($accessibleShortcuts) }} Layar Terbuka)</h2>
                    <p class="text-[11px] text-gray-400">Tautan cepat yang disesuaikan hanya untuk kebutuhan operasional staf divisi {{ session('active_role', 'Sales') }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-[11px] bg-purple-100 text-purple-800 font-semibold px-2 py-0.5 rounded-full">{{ count($accessibleShortcuts) }} Layar</span>
                <i id="accordion-icon" class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200"></i>
            </div>
        </button>

        <div id="scenario-matrix-content" class="hidden p-5 border-t border-gray-200 bg-white">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 text-xs">
                @foreach($accessibleShortcuts as $sc)
                    <a href="{{ route($sc['route']) }}" class="p-2.5 rounded-lg bg-gray-50 border hover:bg-purple-50 flex items-center justify-between transition">
                        <span><strong>{{ $sc['code'] }}</strong> {{ $sc['title'] }}</span>
                        <i class="fa-solid fa-arrow-right text-[10px] text-gray-400"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if(!empty($adminStaffMatrix))
        <!-- Main Admin: Staff Access Matrix & Permission Locator (Point 4) -->
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-xl p-5 text-white shadow-md border border-slate-700" id="admin-staff-matrix">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b border-white/10">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="w-6 h-6 rounded-md bg-purple-500/20 text-purple-300 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-users-gear"></i>
                        </span>
                        <h3 class="font-bold text-sm tracking-wide">Main Admin: Matriks & Inspeksi Hak Akses Staf Divisi</h3>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        Sebagai Main Super Admin, Anda memiliki akses penuh ke seluruh modul dan dapat memantau atau menginspeksi alur kerja staf divisi secara spesifik.
                    </p>
                </div>
                <div class="mt-3 md:mt-0 text-xs bg-white/10 px-3 py-1.5 rounded-lg border border-white/10 text-purple-200">
                    <i class="fa-solid fa-shield-check mr-1 text-emerald-400"></i>Superuser Mode Active
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($adminStaffMatrix as $sKey => $staff)
                    <div class="bg-white/5 hover:bg-white/10 rounded-lg p-3.5 border border-white/10 transition">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="w-7 h-7 rounded-full {{ $staff['avatar_color'] }} text-white text-xs font-bold flex items-center justify-center">
                                    {{ substr($staff['name'], 0, 1) }}
                                </span>
                                <div>
                                    <div class="text-xs font-bold text-white leading-tight">{{ $staff['name'] }}</div>
                                    <div class="text-[10px] text-purple-300 font-mono">{{ $staff['email'] }}</div>
                                </div>
                            </div>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $staff['badge_color'] }}">
                                {{ $staff['role'] }}
                            </span>
                        </div>
                        <div class="text-[11px] text-gray-300 mb-2">
                            <span class="text-gray-400 block text-[10px]">Divisi & Jabatan:</span>
                            {{ $staff['dept'] }} &bull; {{ $staff['role_title'] }}
                        </div>
                        <div class="bg-black/20 p-2 rounded text-[10px] space-y-1 mb-3">
                            <div class="flex justify-between text-gray-300">
                                <span class="text-gray-400">Aplikasi Diizinkan:</span>
                                <strong class="text-purple-300">{{ $staff['allowed_apps_count'] }} Apps</strong>
                            </div>
                            <div class="flex justify-between text-gray-300">
                                <span class="text-gray-400">Shortcut Layar:</span>
                                <strong class="text-purple-300">{{ $staff['allowed_shortcuts_count'] }} Layar</strong>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" onclick="selectRole('{{ $sKey }}')" class="w-full text-center text-xs bg-purple-600/80 hover:bg-purple-600 text-white font-medium py-1.5 px-3 rounded transition flex items-center justify-center space-x-1.5">
                                <i class="fa-solid fa-arrow-right-to-bracket text-[10px]"></i>
                                <span>Inspeksi Akses {{ $staff['role'] }}</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Business Analytics & Financial Overview (Interactive Live Charts) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart 1: Financial & Asset Structure (Donut) -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4 border-b pb-2">
                <div>
                    <h3 class="font-bold text-gray-800 text-sm">Struktur Likuiditas & Aset</h3>
                    <p class="text-[11px] text-gray-400">Komposisi Kas, Stok, Piutang & Utang</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                    <i class="fa-solid fa-circle-check text-[10px] mr-1"></i>Live Ledger
                </span>
            </div>
            <div class="relative h-56 flex items-center justify-center">
                <canvas id="financeChart"></canvas>
            </div>
            <div class="mt-4 pt-3 border-t grid grid-cols-2 gap-2 text-xs">
                <div class="p-2 rounded bg-purple-50">
                    <span class="text-gray-500 block text-[10px]">Kas Bank (1010)</span>
                    <strong class="text-purple-900">Rp{{ number_format($bankBalance, 0, ',', '.') }}</strong>
                </div>
                <div class="p-2 rounded bg-amber-50">
                    <span class="text-gray-500 block text-[10px]">Valuasi Persediaan (1200)</span>
                    <strong class="text-amber-900">Rp{{ number_format($stockValuation, 0, ',', '.') }}</strong>
                </div>
                <div class="p-2 rounded bg-blue-50">
                    <span class="text-gray-500 block text-[10px]">Piutang Usaha (1100)</span>
                    <strong class="text-blue-900">Rp{{ number_format($totalAr, 0, ',', '.') }}</strong>
                </div>
                <div class="p-2 rounded bg-rose-50">
                    <span class="text-gray-500 block text-[10px]">Utang Dagang (2100)</span>
                    <strong class="text-rose-900">Rp{{ number_format($totalAp, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <!-- Chart 2: Product Category Inventory Distribution (Bar Chart) -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between mb-4 border-b pb-2">
                <div>
                    <h3 class="font-bold text-gray-800 text-sm">Distribusi Ketersediaan Persediaan Produk</h3>
                    <p class="text-[11px] text-gray-400">Unit fisik on hand per kategori & perbandingan antar-gudang</p>
                </div>
                <div class="flex items-center space-x-2 text-xs">
                    <span class="px-2 py-0.5 rounded bg-gray-100 font-medium text-gray-700">
                        <i class="fa-solid fa-warehouse mr-1 text-amber-600"></i>WH-JKT: <strong>{{ $totalUnitsJkt }}</strong> unit
                    </span>
                    <span class="px-2 py-0.5 rounded bg-gray-100 font-medium text-gray-700">
                        <i class="fa-solid fa-store mr-1 text-rose-600"></i>ST-BDG: <strong>{{ $totalUnitsBdg }}</strong> unit
                    </span>
                </div>
            </div>
            <div class="relative h-64">
                <canvas id="inventoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chatter for S-01 -->
    @include('partials.odoo_chatter', ['auditLogs' => $recentAudit])
</div>
@endsection

@section('scripts')
<!-- Chart.js for Interactive Visual Dashboard -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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
        // Chart 1: Likuiditas & Aset Donut Chart
        const ctxFinance = document.getElementById('financeChart').getContext('2d');
        new Chart(ctxFinance, {
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
                    backgroundColor: ['#714B67', '#F59E0B', '#3B82F6', '#EF4444'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
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

        // Chart 2: Inventory Distribution Bar Chart
        const ctxInv = document.getElementById('inventoryChart').getContext('2d');
        new Chart(ctxInv, {
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
                        'rgba(113, 75, 103, 0.85)',
                        'rgba(59, 130, 246, 0.85)',
                        'rgba(16, 185, 129, 0.85)',
                        'rgba(245, 158, 11, 0.85)',
                        'rgba(236, 72, 153, 0.85)'
                    ],
                    borderRadius: 6,
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
    });
</script>
@endsection
