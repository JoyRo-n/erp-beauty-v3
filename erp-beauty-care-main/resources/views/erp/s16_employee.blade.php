@extends('layouts.odoo')

@section('title', 'S-16 Employees dan Skill Matrix')
@section('breadcrumb', 'Employees > Therapist Skill Matrix')

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Employees & Skill Matrix</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Service Assignment Verification (SR-17 / DL-14)</span>
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

    <!-- Main Employee Card (Matching S-16 Wireframe) -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        @if($selectedEmployee)
            <div class="border-b pb-4 mb-4 flex justify-between items-start">
                <div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-rose-100 text-rose-800">Employee Card</span>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $selectedEmployee->id }} — {{ $selectedEmployee->name }}</h2>
                    <div class="text-xs text-gray-500">{{ $selectedEmployee->job_position }} &bull; {{ $selectedEmployee->department }}</div>
                </div>
                <div>
                    <span class="px-3 py-1 rounded text-xs font-bold {{ $selectedEmployee->status === 'Aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $selectedEmployee->status }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm mb-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Employee ID</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800 font-mono font-bold">{{ $selectedEmployee->id }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Department</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800">{{ $selectedEmployee->department }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Position</label>
                    <div class="p-2 bg-gray-50 rounded border text-gray-800">{{ $selectedEmployee->job_position }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Skill Matrix (SR-17)</label>
                    <div class="p-2 bg-purple-50 rounded border border-purple-200 text-purple-900 font-bold flex flex-wrap gap-1">
                        @php $skills = is_array($selectedEmployee->skills) ? $selectedEmployee->skills : explode(';', $selectedEmployee->skills); @endphp
                        @foreach($skills as $s)
                            <span class="px-2 py-0.5 rounded bg-purple-200 text-purple-800 text-xs">{{ trim($s) }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- All 4 Employees Table (S-16) -->
        <div class="mt-8 border-t pt-6">
            <h3 class="text-xs font-semibold uppercase text-gray-500 mb-3">All Studio & Warehouse Employees</h3>
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2.5">ID</th>
                            <th class="p-2.5">Name</th>
                            <th class="p-2.5">Position</th>
                            <th class="p-2.5">Department</th>
                            <th class="p-2.5">Skills</th>
                            <th class="p-2.5">Facial Qualified?</th>
                            <th class="p-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($employees as $e)
                            <tr class="hover:bg-rose-50/40 {{ $selectedEmployee && $selectedEmployee->id === $e->id ? 'bg-rose-50 font-medium' : '' }}">
                                <td class="p-2.5 font-mono text-purple-700">
                                    <a href="{{ route('s16.employee', ['emp' => $e->id]) }}">{{ $e->id }}</a>
                                </td>
                                <td class="p-2.5 font-semibold">{{ $e->name }}</td>
                                <td class="p-2.5">{{ $e->job_position }}</td>
                                <td class="p-2.5">{{ $e->department }}</td>
                                <td class="p-2.5">
                                    @php $sk = is_array($e->skills) ? $e->skills : explode(';', $e->skills); @endphp
                                    {{ implode(', ', array_map('trim', $sk)) }}
                                </td>
                                <td class="p-2.5">
                                    @if($e->hasSkill('Facial'))
                                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold"><i class="fa-solid fa-check mr-1"></i> Qualified</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-500"><i class="fa-solid fa-xmark mr-1"></i> No Facial</span>
                                    @endif
                                </td>
                                <td class="p-2.5"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold">{{ $e->status }}</span></td>
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
                Registrasi Karyawan & Matriks Keahlian Baru (SR-17 & SR-22)
            </h3>
            <form action="{{ route('s16.create') }}" method="POST" class="bg-gray-50 p-4 rounded-lg border text-xs grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
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
