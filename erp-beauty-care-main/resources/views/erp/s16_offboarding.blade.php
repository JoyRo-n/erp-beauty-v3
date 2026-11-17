@extends('layouts.odoo')

@section('title', 'HR Employee Offboarding & Termination Procedure')
@section('breadcrumb', 'Employees > HR Offboarding & Termination Procedure')

@section('content')
<div class="space-y-5">
    <!-- Top Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3.5 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2.5">
            <a href="{{ route('s16.employee', ['emp' => $selectedEmployee->id ?? 'E001']) }}" class="px-2.5 py-1 rounded bg-[#FFF0F5] hover:bg-[#FADADD] border border-pink-200 text-rose-900 text-xs font-bold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to Employee Matrix
            </a>
            <span class="text-gray-300">|</span>
            <span class="font-bold text-gray-800 text-sm">Official Corporate HR Offboarding &amp; Termination Procedure (SOP PHK)</span>
        </div>

        <form method="GET" action="{{ route('s16.offboarding') }}" class="flex items-center space-x-2">
            <label class="text-xs font-semibold text-rose-900">Select Employee:</label>
            <select name="emp" onchange="this.form.submit()" class="text-xs border rounded-lg p-1.5 bg-white font-semibold text-rose-950 focus:outline-none">
                @foreach($employees as $e)
                    <option value="{{ $e->id }}" {{ $selectedEmployee && $selectedEmployee->id === $e->id ? 'selected' : '' }}>
                        {{ $e->id }} — {{ $e->name }} ({{ $e->status }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- 5-Stage Corporate Offboarding Workflow Stepper -->
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
        <h3 class="text-xs font-bold uppercase tracking-wider text-rose-700 mb-3">
            Standard Corporate Termination &amp; Offboarding Workflow (5 Mandatory Stages)
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 text-xs">
            <div class="p-3 rounded-xl bg-[#FFF5F8] border border-pink-200">
                <div class="font-extrabold text-rose-700">Stage 1 &bull; SP1–SP3 / Notice</div>
                <div class="text-[11px] text-gray-600 mt-1">Verify warning letters (SP1, SP2, SP3), performance review, or formal termination grounds.</div>
            </div>
            <div class="p-3 rounded-xl bg-[#FFF5F8] border border-pink-200">
                <div class="font-extrabold text-rose-700">Stage 2 &bull; Exit Interview</div>
                <div class="text-[11px] text-gray-600 mt-1">Conduct HR &amp; Department Supervisor exit interview and sign confidentiality reminder.</div>
            </div>
            <div class="p-3 rounded-xl bg-[#FFF5F8] border border-pink-200">
                <div class="font-extrabold text-rose-700">Stage 3 &bull; Asset Return</div>
                <div class="text-[11px] text-gray-600 mt-1">Collect studio uniform, locker keys, ID badge, and beauty equipment handover (BAST).</div>
            </div>
            <div class="p-3 rounded-xl bg-[#FFF5F8] border border-pink-200">
                <div class="font-extrabold text-rose-700">Stage 4 &bull; Access Revocation</div>
                <div class="text-[11px] text-gray-600 mt-1">Revoke NusaGlow ERP login credentials, treatment assignment eligibility, and building access.</div>
            </div>
            <div class="p-3 rounded-xl bg-[#FFF5F8] border border-pink-200">
                <div class="font-extrabold text-rose-700">Stage 5 &bull; Final Settlement</div>
                <div class="text-[11px] text-gray-600 mt-1">Calculate final pro-rata salary, severance pay (Uang Pesangon), and issue Paklaring letter.</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Selected Employee Personal & Employment Dossier -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-4">
            @if($selectedEmployee)
                <div class="flex items-center justify-between border-b pb-3">
                    <div>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-800">{{ $selectedEmployee->id }}</span>
                        <h2 class="text-lg font-bold text-gray-800 mt-1">{{ $selectedEmployee->name }}</h2>
                        <div class="text-xs text-gray-500">{{ $selectedEmployee->job_position }} &bull; {{ $selectedEmployee->department }}</div>
                    </div>
                    <span class="px-2.5 py-1 rounded text-xs font-bold {{ $selectedEmployee->status === 'Aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $selectedEmployee->status }}
                    </span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="p-2.5 rounded-lg bg-gray-50 border">
                        <span class="text-gray-400 block text-[10px]">Birthdate (Tanggal Lahir)</span>
                        <strong class="text-gray-800 font-mono">{{ $selectedEmployee->birthdate ?: '1997-04-12' }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-gray-50 border">
                        <span class="text-gray-400 block text-[10px]">Phone Number (No. Telepon)</span>
                        <strong class="text-gray-800 font-mono">{{ $selectedEmployee->phone ?: '+62 812-8401-2201' }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-gray-50 border">
                        <span class="text-gray-400 block text-[10px]">Home Address (Alamat Rumah)</span>
                        <strong class="text-gray-800">{{ $selectedEmployee->address ?: 'Jl. Dago Asri No. 14, Coblong, Kota Bandung' }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-gray-50 border">
                        <span class="text-gray-400 block text-[10px]">Certified Skills</span>
                        <strong class="text-rose-800">{{ implode(', ', is_array($selectedEmployee->skills) ? $selectedEmployee->skills : [$selectedEmployee->skills]) }}</strong>
                    </div>
                </div>

                @if($selectedEmployee->status !== 'Aktif')
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs space-y-1.5">
                        <div class="font-bold text-rose-900 flex items-center">
                            <i class="fa-solid fa-ban mr-1.5 text-rose-600"></i>Employment Terminated (Nonaktif)
                        </div>
                        @if($selectedEmployee->termination_notice_type)
                            <div><strong>Notice Type:</strong> {{ $selectedEmployee->termination_notice_type }}</div>
                        @endif
                        @if($selectedEmployee->termination_reason)
                            <div><strong>Reason:</strong> {{ $selectedEmployee->termination_reason }}</div>
                        @endif
                        @if($selectedEmployee->severance_amount > 0)
                            <div><strong>Final Settlement:</strong> Rp{{ number_format($selectedEmployee->severance_amount, 0, ',', '.') }}</div>
                        @endif
                        @if($selectedEmployee->terminated_at)
                            <div class="text-[10px] text-rose-500">Processed at: {{ $selectedEmployee->terminated_at }}</div>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        <!-- Right 2 Columns: Official Termination Execution Form -->
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            @if($selectedEmployee && $selectedEmployee->status === 'Aktif')
                <h3 class="text-base font-bold text-rose-950 mb-1 flex items-center">
                    <i class="fa-solid fa-file-contract text-rose-600 mr-2"></i>
                    Execute Official Employee Termination &amp; Offboarding
                </h3>
                <p class="text-xs text-gray-500 mb-4">
                    In accordance with corporate HR governance, an employee cannot be fired arbitrarily. All 4 mandatory clearance steps below must be verified and checked before termination is finalized.
                </p>

                <form action="{{ route('s16.terminate') }}" method="POST" class="space-y-4 text-xs" onsubmit="return confirm('Confirm official HR termination and offboarding for {{ $selectedEmployee->name }} ({{ $selectedEmployee->id }})?');">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $selectedEmployee->id }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">1. Official Notice &amp; Legal Classification</label>
                            <select name="termination_notice_type" required class="w-full border rounded-lg p-2.5 bg-white focus:outline-none">
                                <option value="SP3 Final Warning & Disciplinary Termination">SP3 Final Warning &amp; Disciplinary Termination</option>
                                <option value="Performance Evaluation Failure (PIP Unmet)">Performance Evaluation Failure (PIP Unmet)</option>
                                <option value="End of Fixed-Term Contract (PKWT Expired)">End of Fixed-Term Contract (PKWT Expired)</option>
                                <option value="Mutual Separation Agreement (Perjanjian Bersama)">Mutual Separation Agreement (Perjanjian Bersama)</option>
                                <option value="Severe SOP / Safety Violation">Severe SOP / Hygiene Safety Violation</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">2. Final Severance &amp; Pro-Rata Settlement (Rp)</label>
                            <input type="number" name="severance_amount" value="4500000" min="0" step="50000" required class="w-full border rounded-lg p-2.5 bg-white font-mono focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">3. Formal Evaluation &amp; Termination Justification</label>
                        <textarea name="termination_reason" rows="3" required placeholder="Document the specific warning letter references (SP1/SP2/SP3), attendance/SOP violations, or contract completion details..." class="w-full border rounded-lg p-2.5 bg-white focus:outline-none"></textarea>
                    </div>

                    <!-- Mandatory 4-Point Office Clearance Checklist -->
                    <div class="p-4 rounded-xl bg-[#FFF5F8] border border-[#F2B6CE] space-y-2.5">
                        <div class="font-bold text-rose-950 text-xs uppercase tracking-wider">
                            4. Mandatory Corporate Offboarding Clearance Checklist (All 4 Required)
                        </div>
                        <label class="flex items-start space-x-2.5 cursor-pointer">
                            <input type="checkbox" name="checklist[]" value="Warning Letters & HR Evaluation Verified" required class="mt-0.5 rounded text-rose-600">
                            <span><strong>HR Warning &amp; Exit Interview Completed:</strong> SP1–SP3 / performance dossier reviewed with Department Supervisor and formal exit interview signed.</span>
                        </label>
                        <label class="flex items-start space-x-2.5 cursor-pointer">
                            <input type="checkbox" name="checklist[]" value="Company Assets & Uniform Returned" required class="mt-0.5 rounded text-rose-600">
                            <span><strong>Company Assets &amp; Inventory Handover (BAST):</strong> Studio uniform, ID card, locker keys, and assigned skincare tools returned in good condition.</span>
                        </label>
                        <label class="flex items-start space-x-2.5 cursor-pointer">
                            <input type="checkbox" name="checklist[]" value="ERP Access & Scheduling Privileges Revoked" required class="mt-0.5 rounded text-rose-600">
                            <span><strong>IT &amp; ERP System Access Revocation:</strong> Revoke active therapist scheduling eligibility (SR-17 blocks `Nonaktif` staff from Beauty Treatments).</span>
                        </label>
                        <label class="flex items-start space-x-2.5 cursor-pointer">
                            <input type="checkbox" name="checklist[]" value="Final Payroll, Severance & Paklaring Issued" required class="mt-0.5 rounded text-rose-600">
                            <span><strong>Final Financial Rights &amp; Paklaring Approved:</strong> Final salary, severance (Uang Pesangon/Penghargaan), and certificate of employment prepared.</span>
                        </label>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-5 rounded-xl shadow transition flex items-center space-x-2">
                            <i class="fa-solid fa-user-slash"></i>
                            <span>Finalize Offboarding &amp; Terminate Employee ({{ $selectedEmployee->id }})</span>
                        </button>
                    </div>
                </form>
            @else
                <div class="p-6 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto text-xl">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-800">Employee {{ $selectedEmployee->name ?? '' }} is Already Offboarded (`Nonaktif`)</h3>
                    <p class="text-xs text-gray-500 max-w-md mx-auto">
                        Per corporate rule <strong>SR-17</strong>, non-active employees are automatically blocked from being assigned to new Studio Beauty Treatments. Select another active employee from the dropdown above to process an offboarding.
                    </p>
                </div>
            @endif

            <!-- Offboarded / Nonaktif Employees Registry -->
            <div class="mt-8 border-t pt-5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">
                    Offboarded / Non-Active Employees Registry ({{ $terminatedEmployees->count() }})
                </h4>
                <div class="overflow-x-auto border rounded-lg">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th class="p-2">ID</th>
                                <th class="p-2">Name</th>
                                <th class="p-2">Department</th>
                                <th class="p-2">Notice Classification</th>
                                <th class="p-2 text-right">Settlement</th>
                                <th class="p-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($terminatedEmployees as $te)
                                <tr class="hover:bg-rose-50/40">
                                    <td class="p-2 font-mono font-bold text-rose-700">
                                        <a href="{{ route('s16.offboarding', ['emp' => $te->id]) }}">{{ $te->id }}</a>
                                    </td>
                                    <td class="p-2 font-semibold">{{ $te->name }}</td>
                                    <td class="p-2">{{ $te->department }}</td>
                                    <td class="p-2 text-gray-600">{{ $te->termination_notice_type ?: 'Contract Ended / Nonaktif' }}</td>
                                    <td class="p-2 text-right font-mono">Rp{{ number_format($te->severance_amount ?? 0, 0, ',', '.') }}</td>
                                    <td class="p-2"><span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold">{{ $te->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-3 text-center text-gray-400 italic">No terminated employees recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('partials.odoo_chatter')
</div>
@endsection
