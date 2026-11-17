@extends('layouts.odoo')

@section('title', 'S-16 Employees dan Skill Matrix')
@section('breadcrumb', 'Employees > Therapist Skill Matrix')

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex flex-wrap items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Employees &amp; Skill Matrix</span>
            <span class="text-gray-400">|</span>
            <a href="{{ route('s17.treatment') }}" class="text-xs text-rose-700 hover:underline font-medium">Beauty Treatments</a>
            <span class="text-gray-300">&bull;</span>
            <a href="{{ route('s16.offboarding', ['emp' => $selectedEmployee->id ?? 'E001']) }}" class="text-xs bg-rose-600 hover:bg-rose-700 text-white font-bold px-2.5 py-1 rounded-md shadow-xs transition inline-flex items-center space-x-1">
                <i class="fa-solid fa-user-xmark text-[10px]"></i>
                <span>HR Offboarding / Fire Employee Procedure</span>
            </a>
        </div>
        <form method="GET" action="{{ route('s16.employee') }}">
            <select name="emp" onchange="this.form.submit()" class="text-xs border rounded p-1.5 bg-gray-50 focus:outline-none">
                @foreach($employees as $e)
                    <option value="{{ $e->id }}" {{ $selectedEmployee && $selectedEmployee->id === $e->id ? 'selected' : '' }}>
                        {{ $e->id }} — {{ $e->name }} ({{ implode(', ', is_array($e->skills) ? $e->skills : [$e->skills]) }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Main Employee Card (Matching S-16 Wireframe + Personal Dossier + Complete Qualification Breakdown) -->
    @php
        $masterCompetencies = ['Facial', 'Hair Treatment', 'Skin Analysis', 'Stock Handling'];
    @endphp
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        @if($selectedEmployee)
            @php
                $rawSelectedSkills = is_array($selectedEmployee->skills) ? $selectedEmployee->skills : explode(';', (string) $selectedEmployee->skills);
                $selectedSkillList = array_values(array_filter(array_map('trim', $rawSelectedSkills)));
                $qualifiedSkills = [];
                $notQualifiedSkills = [];
                foreach ($masterCompetencies as $comp) {
                    if ($selectedEmployee->hasSkill($comp)) {
                        $qualifiedSkills[] = $comp;
                    } else {
                        $notQualifiedSkills[] = $comp;
                    }
                }
                foreach ($selectedSkillList as $extraSk) {
                    if (!in_array($extraSk, $qualifiedSkills, true)) {
                        $qualifiedSkills[] = $extraSk;
                    }
                }
            @endphp
            <div class="border-b pb-4 mb-4 flex flex-wrap justify-between items-start gap-3">
                <div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-rose-100 text-rose-800">Employee Personal &amp; Skill Profile</span>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $selectedEmployee->id }} — {{ $selectedEmployee->name }}</h2>
                    <div class="text-xs text-gray-500">{{ $selectedEmployee->job_position }} &bull; {{ $selectedEmployee->department }}</div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1 rounded text-xs font-bold {{ $selectedEmployee->status === 'Aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $selectedEmployee->status }}
                    </span>
                    @if($selectedEmployee->status === 'Aktif')
                        <a href="{{ route('s16.offboarding', ['emp' => $selectedEmployee->id]) }}" class="px-3 py-1 rounded bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 text-xs font-bold transition flex items-center space-x-1">
                            <i class="fa-solid fa-file-signature text-[10px]"></i>
                            <span>Fire / Offboard {{ $selectedEmployee->name }}</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Row 1: Employment & Personal Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Employee ID</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-mono font-bold">{{ $selectedEmployee->id }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Full Name</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-semibold">{{ $selectedEmployee->name }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Department &amp; Position</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800">{{ $selectedEmployee->department }} &bull; {{ $selectedEmployee->job_position }}</div>
                </div>
            </div>

            <!-- Row 2: Personal Contact Details (Birthdate, Phone Number, Home Address) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-4 bg-[#FFF9FB] p-4 rounded-xl border border-pink-100">
                <div>
                    <label class="block text-xs font-semibold text-rose-700 mb-1">
                        <i class="fa-solid fa-cake-candles mr-1 text-rose-500"></i>Birthdate (Tanggal Lahir)
                    </label>
                    <div class="p-2 bg-white rounded border border-pink-200 text-gray-800 font-mono">
                        {{ $selectedEmployee->birthdate ?: '1997-04-12' }}
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-rose-700 mb-1">
                        <i class="fa-solid fa-phone mr-1 text-rose-500"></i>Phone Number (No. Telepon)
                    </label>
                    <div class="p-2 bg-white rounded border border-pink-200 text-gray-800 font-mono">
                        {{ $selectedEmployee->phone ?: '+62 812-8401-2201' }}
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-rose-700 mb-1">
                        <i class="fa-solid fa-house-user mr-1 text-rose-500"></i>Home Address (Alamat Rumah)
                    </label>
                    <div class="p-2 bg-white rounded border border-pink-200 text-gray-800">
                        {{ $selectedEmployee->address ?: 'Jl. Dago Asri No. 14, Coblong, Kota Bandung' }}
                    </div>
                </div>
            </div>

            <!-- Row 3: Complete Competency Qualification Breakdown (Qualified vs Not Qualified) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs mb-6">
                <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200">
                    <div class="font-bold text-emerald-900 uppercase tracking-wider text-[11px] mb-2 flex items-center">
                        <i class="fa-solid fa-circle-check text-emerald-600 mr-1.5"></i>
                        Qualified In ({{ count($qualifiedSkills) }} Competencies)
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($qualifiedSkills as $qSk)
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-100 border border-emerald-300 text-emerald-900 font-bold flex items-center">
                                <i class="fa-solid fa-check mr-1 text-emerald-600 text-[10px]"></i> {{ $qSk }}
                            </span>
                        @empty
                            <span class="text-gray-400 italic">None</span>
                        @endforelse
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-rose-50/60 border border-rose-200">
                    <div class="font-bold text-rose-900 uppercase tracking-wider text-[11px] mb-2 flex items-center">
                        <i class="fa-solid fa-circle-xmark text-rose-600 mr-1.5"></i>
                        Not Qualified In ({{ count($notQualifiedSkills) }} Competencies)
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($notQualifiedSkills as $nqSk)
                            <span class="px-2.5 py-1 rounded-lg bg-white border border-rose-200 text-rose-700 font-semibold flex items-center">
                                <i class="fa-solid fa-xmark mr-1 text-rose-500 text-[10px]"></i> No {{ $nqSk }}
                            </span>
                        @empty
                            <span class="text-emerald-700 font-semibold">Qualified in all standard competencies</span>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        <!-- All Employees Table (Click ID or Name to inspect personal details) -->
        <div class="mt-8 border-t pt-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold uppercase text-gray-500">All Studio &amp; Warehouse Employees (Complete Qualification Breakdown)</h3>
                <a href="{{ route('s16.offboarding') }}" class="text-xs text-rose-600 hover:underline font-bold">
                    <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i>Open HR Offboarding &amp; Termination Portal
                </a>
            </div>
            <div class="overflow-x-auto border rounded-lg max-h-[32rem] overflow-y-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600 sticky top-0 z-10">
                        <tr>
                            <th class="p-2.5">ID</th>
                            <th class="p-2.5">Name</th>
                            <th class="p-2.5">Position &amp; Dept</th>
                            <th class="p-2.5">Qualified In (Certified Skills)</th>
                            <th class="p-2.5">Not Qualified In (Restricted From)</th>
                            <th class="p-2.5">Status</th>
                            <th class="p-2.5 text-right">HR Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($employees as $e)
                            @php
                                $eSkillsRaw = is_array($e->skills) ? $e->skills : explode(';', (string) $e->skills);
                                $eSkills = array_values(array_filter(array_map('trim', $eSkillsRaw)));
                                $eQualified = [];
                                $eNotQualified = [];
                                foreach ($masterCompetencies as $mc) {
                                    if ($e->hasSkill($mc)) {
                                        $eQualified[] = $mc;
                                    } else {
                                        $eNotQualified[] = $mc;
                                    }
                                }
                                foreach ($eSkills as $esk) {
                                    if (!in_array($esk, $eQualified, true)) {
                                        $eQualified[] = $esk;
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-rose-50/40 cursor-pointer {{ $selectedEmployee && $selectedEmployee->id === $e->id ? 'bg-rose-50 font-medium' : '' }}"
                                onclick="window.location='{{ route('s16.employee', ['emp' => $e->id]) }}'">
                                <td class="p-2.5 font-mono text-rose-700 font-bold">
                                    <a href="{{ route('s16.employee', ['emp' => $e->id]) }}">{{ $e->id }}</a>
                                </td>
                                <td class="p-2.5 font-semibold text-rose-950 hover:text-rose-600 underline decoration-pink-300">
                                    <a href="{{ route('s16.employee', ['emp' => $e->id]) }}">{{ $e->name }}</a>
                                </td>
                                <td class="p-2.5">
                                    <div class="font-medium text-gray-800">{{ $e->job_position }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $e->department }}</div>
                                </td>
                                <td class="p-2.5">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($eQualified as $qItem)
                                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[11px] inline-flex items-center">
                                                <i class="fa-solid fa-check mr-1 text-[9px]"></i> {{ $qItem }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="p-2.5">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($eNotQualified as $nqItem)
                                            <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-500 border border-gray-200 text-[11px] inline-flex items-center">
                                                <i class="fa-solid fa-xmark mr-1 text-rose-400 text-[9px]"></i> No {{ $nqItem }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="p-2.5">
                                    <span class="px-2 py-0.5 rounded font-semibold {{ $e->status === 'Aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $e->status }}
                                    </span>
                                </td>
                                <td class="p-2.5 text-right" onclick="event.stopPropagation()">
                                    @if($e->status === 'Aktif')
                                        <a href="{{ route('s16.offboarding', ['emp' => $e->id]) }}" class="px-2 py-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold text-[11px]">
                                            Offboard
                                        </a>
                                    @else
                                        <a href="{{ route('s16.offboarding', ['emp' => $e->id]) }}" class="text-gray-400 hover:underline text-[11px]">
                                            Terminated
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create New Employee Form (Interactive Addition) -->
        <div class="mt-8 border-t pt-6">
            <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                <i class="fa-solid fa-user-plus text-rose-600 mr-2"></i>
                Registrasi Karyawan &amp; Matriks Keahlian Baru
            </h3>
            <form action="{{ route('s16.create') }}" method="POST" class="bg-gradient-to-r from-[#FFF5F8] via-[#FFF9FB] to-white p-4 rounded-xl border border-[#F2C6D8] text-xs grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                @csrf
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Contoh: Maya Indah" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Jabatan (Job Position)</label>
                    <input type="text" name="job_position" required placeholder="Beauty Therapist / Operator" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Departemen</label>
                    <select name="department" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                        <option value="Studio Bandung">Studio Bandung</option>
                        <option value="Gudang Jakarta">Gudang Jakarta</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Tanggal Lahir</label>
                    <input type="date" name="birthdate" value="1997-05-20" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">No. Telepon</label>
                    <input type="text" name="phone" placeholder="+62 812-8899-1020" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-medium text-gray-600 mb-1">Alamat Rumah</label>
                    <input type="text" name="address" placeholder="Jl. Braga No. 25, Kota Bandung" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Keahlian (Skills)</label>
                    <div class="flex items-center space-x-3 mt-1.5">
                        <label class="flex items-center space-x-1">
                            <input type="checkbox" name="skills[]" value="Facial" checked class="rounded text-rose-600">
                            <span>Facial</span>
                        </label>
                        <label class="flex items-center space-x-1">
                            <input type="checkbox" name="skills[]" value="Hair Treatment" class="rounded text-rose-600">
                            <span>Hair Treatment</span>
                        </label>
                        <label class="flex items-center space-x-1">
                            <input type="checkbox" name="skills[]" value="Stock Handling" class="rounded text-rose-600">
                            <span>Stock</span>
                        </label>
                    </div>
                </div>
                <div class="sm:col-span-2 md:col-span-4 flex justify-end">
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-1.5 px-4 rounded shadow transition">
                        <i class="fa-solid fa-plus mr-1"></i> Daftarkan Karyawan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
