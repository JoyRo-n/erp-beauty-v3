@extends('layouts.odoo')

@section('title', 'S-17 Beauty Treatment Completion')
@section('breadcrumb', 'Employees > Studio > ' . ($selectedTreatment->treatment_number ?? 'Treatment'))

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Beauty Treatment Completion</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Consumes 1 BC-MSK-001 at ST-BDG</span>
        </div>
        <form method="GET" action="{{ route('s17.treatment') }}">
            <select name="trt" onchange="this.form.submit()" class="text-xs border rounded p-1.5 bg-gray-50 focus:outline-none">
                @foreach($treatments as $t)
                    <option value="{{ $t->treatment_number }}" {{ $selectedTreatment && $selectedTreatment->treatment_number === $t->treatment_number ? 'selected' : '' }}>
                        {{ $t->treatment_number }} — {{ $t->customer->name ?? $t->customer_id }} ({{ $t->status }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Main Treatment Form (Matching S-17 Wireframe) -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <!-- Status Bar -->
        <div class="flex flex-wrap items-center justify-between border-b pb-4 mb-4 gap-3">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Status:</span>
                <div class="inline-flex rounded-md shadow-sm">
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedTreatment->status ?? '') === 'Draft' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-l">
                        Draft
                    </span>
                    <span class="px-3 py-1 text-xs font-bold {{ ($selectedTreatment->status ?? '') === 'Done' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }} rounded-r">
                        Done
                    </span>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                @php
                    $canOpenInv = in_array($user->role, ['Admin', 'Owner', 'Finance']) || \App\Services\RoleAccessService::hasGrantForFeature($user->role, 'Customer Invoice');
                @endphp
                @if($selectedTreatment && $selectedTreatment->status !== 'Done')
                    @if($canManageTreatment ?? false)
                        <form action="{{ route('s17.complete', $selectedTreatment->treatment_number) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow">
                                <i class="fa-solid fa-sparkles mr-1"></i> Complete Treatment
                            </button>
                        </form>
                    @else
                        <span class="px-3 py-1.5 rounded bg-gray-100 text-gray-600 text-xs font-medium border">
                            <i class="fa-solid fa-lock mr-1 text-gray-400"></i> Penyelesaian hanya oleh Studio
                        </span>
                    @endif
                @elseif($selectedTreatment && $selectedTreatment->status === 'Done')
                    <span class="text-xs px-3 py-1.5 rounded bg-emerald-100 text-emerald-800 font-bold">
                        <i class="fa-solid fa-circle-check mr-1"></i> Treatment Completed & Mask Deducted
                    </span>
                    @if($selectedTreatment->invoice_id)
                        @if($canOpenInv)
                            <a href="{{ route('s08.invoice', ['inv' => $selectedTreatment->invoice_id]) }}" class="text-xs px-3 py-1.5 rounded bg-teal-50 border border-teal-300 text-teal-800 font-bold hover:bg-teal-100">
                                <i class="fa-solid fa-file-invoice mr-1"></i> View Invoice #{{ $selectedTreatment->invoice_id }}
                            </a>
                        @else
                            <span class="text-xs px-3 py-1.5 rounded bg-teal-50 border border-teal-200 text-teal-800 font-bold">
                                <i class="fa-solid fa-file-invoice mr-1"></i> Invoice #{{ $selectedTreatment->invoice_id }}
                            </span>
                        @endif
                    @endif
                @endif
            </div>
        </div>

        @if($selectedTreatment)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm mb-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Treatment #</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-bold font-mono">{{ $selectedTreatment->treatment_number }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Service</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-semibold">{{ $selectedTreatment->service_id }} — Glow Facial</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Customer</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800">{{ $selectedTreatment->customer->name ?? $selectedTreatment->customer_id }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Therapist (E001 Facial)</label>
                    <div class="p-2 bg-purple-50 rounded border border-purple-200 text-purple-900 font-bold">
                        {{ $selectedTreatment->employee->name ?? $selectedTreatment->employee_id }} (Qualified)
                    </div>
                </div>
            </div>

            <!-- Consumption & Billing Grid (S-17) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4 bg-gray-50 border rounded-lg mb-6">
                <div>
                    <span class="text-xs text-gray-500 block">Consumed SKU (Stock Move)</span>
                    <strong class="text-base text-gray-800 font-mono">{{ $selectedTreatment->consumed_sku }}</strong>
                    <span class="text-xs text-gray-500 block mt-0.5">Location: {{ $selectedTreatment->location_code }} (1 Sachet Mask)</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Standard Cost / COGS</span>
                    <strong class="text-base text-blue-700">Rp40.000</strong>
                    <span class="text-xs text-gray-500 block mt-0.5">Account 5010 Treatment Consumables</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Billed Total (Service + Tax)</span>
                    <strong class="text-base text-emerald-700">Rp333.000</strong>
                    <span class="text-xs text-gray-500 block mt-0.5">Rp300.000 service + Rp33.000 PPN 11%</span>
                </div>
            </div>
        @else
            <div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <div class="w-12 h-12 mx-auto rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-xl mb-3">
                    <i class="fa-solid fa-spa"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-sm mb-1">Belum Ada Layanan Treatment</h4>
                @if($canManageTreatment ?? false)
                    <p class="text-gray-500 text-xs mb-4">Klik tombol di bawah untuk membuat dan menyelesaikan layanan Glow Facial untuk Rani Kartika (C004) dengan terapis Sinta Maharani (E001), atau jadwalkan layanan baru di formulir bawah.</p>
                    <form action="{{ route('s17.complete', 'TRT/2026/00007') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md transition">
                            <i class="fa-solid fa-sparkles mr-1.5"></i> Selesaikan Treatment TRT/2026/00007
                        </button>
                    </form>
                @else
                    <p class="text-gray-500 text-xs">Belum ada layanan treatment yang dijadwalkan atau diselesaikan oleh tim Studio.</p>
                @endif
            </div>
        @endif

        <!-- Form Tambah Treatment Baru (Studio / Superuser / Granted) -->
        @if($canManageTreatment ?? false)
            <div class="mt-8 border-t pt-6">
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                    <i class="fa-solid fa-calendar-plus text-purple-600 mr-2"></i>
                    Jadwalkan Layanan Treatment Baru (Glow Facial)
                </h3>
                <form action="{{ route('s17.create') }}" method="POST" class="bg-gray-50 p-4 rounded-lg border text-xs grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @csrf
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Pelanggan (Customer)</label>
                        <select name="customer_id" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ $c->id === 'C004' ? 'selected' : '' }}>
                                    {{ $c->id }} - {{ $c->name }} ({{ $c->city }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-600 mb-1">Terapis Bertugas (Wajib Skill Facial)</label>
                        <select name="employee_id" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $emp->id === 'E001' ? 'selected' : '' }}>
                                    {{ $emp->id }} - {{ $emp->name }} ({{ is_array($emp->skills) ? implode(', ', $emp->skills) : $emp->skills }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-1.5 px-3 rounded shadow transition">
                            <i class="fa-solid fa-plus mr-1"></i> Buat Treatment (Draft)
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
