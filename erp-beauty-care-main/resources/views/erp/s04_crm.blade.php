@extends('layouts.odoo')

@section('title', 'S-04 CRM Pipeline Kanban')
@section('breadcrumb', 'CRM > Pipeline')

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">CRM Pipeline</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Views: <strong>Kanban</strong> &bull; List &bull; Form</span>
        </div>
        <div class="text-xs text-gray-500">
            Salesperson: <strong>Kevin Mahendra</strong>
        </div>
    </div>

    <!-- Kanban Stages Columns (Matching S-04 Wireframe) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Stage 1: New -->
        <div class="bg-gray-100 rounded-lg p-3 border border-gray-200">
            <div class="flex items-center justify-between font-bold text-xs uppercase text-gray-600 mb-3">
                <span>New</span>
                <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-700">
                    {{ $leads->where('stage', 'New')->count() }}
                </span>
            </div>
            <div class="space-y-3">
                @forelse($leads->where('stage', 'New') as $lead)
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-mono text-xs font-bold text-purple-700">{{ $lead->id }}</span>
                            <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-700 font-semibold">{{ $lead->status }}</span>
                        </div>
                        <div class="font-bold text-gray-800 text-sm">{{ $lead->name }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            <i class="fa-solid fa-user-tie mr-1 text-gray-400"></i> {{ $lead->salesperson }}
                        </div>
                        <div class="mt-3 pt-2 border-t flex justify-end">
                            <form action="{{ route('s04.convert', $lead->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded bg-purple-600 hover:bg-purple-700 text-white text-xs font-medium">
                                    <i class="fa-solid fa-arrow-right-arrow-left mr-1"></i> Convert to Opp
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-gray-400 italic text-center py-4">No leads in New stage</div>
                @endforelse
            </div>
        </div>

        <!-- Stage 2: Qualified -->
        <div class="bg-gray-100 rounded-lg p-3 border border-gray-200">
            <div class="flex items-center justify-between font-bold text-xs uppercase text-gray-600 mb-3">
                <span>Qualified</span>
                <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-700">
                    {{ $leads->where('stage', 'Qualified')->count() }}
                </span>
            </div>
            <div class="space-y-3">
                @forelse($leads->where('stage', 'Qualified') as $lead)
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-mono text-xs font-bold text-purple-700">{{ $lead->id }}</span>
                            <span class="text-xs px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 font-semibold">{{ $lead->status }}</span>
                        </div>
                        <div class="font-bold text-gray-800 text-sm">{{ $lead->name }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            <i class="fa-solid fa-user-tie mr-1 text-gray-400"></i> {{ $lead->salesperson }}
                        </div>
                        <div class="mt-3 pt-2 border-t flex justify-between items-center text-xs">
                            <span class="text-emerald-600 font-medium"><i class="fa-solid fa-circle-check"></i> Qualified</span>
                            <a href="{{ route('s05.sales') }}" class="text-purple-600 hover:underline">New Quotation &rarr;</a>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-gray-400 italic text-center py-4">No opportunities in Qualified stage</div>
                @endforelse
            </div>
        </div>

        <!-- Stage 3: Proposition -->
        <div class="bg-gray-100 rounded-lg p-3 border border-gray-200">
            <div class="flex items-center justify-between font-bold text-xs uppercase text-gray-600 mb-3">
                <span>Proposition</span>
                <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-700">
                    {{ $leads->where('stage', 'Proposition')->count() }}
                </span>
            </div>
            <div class="text-xs text-gray-400 italic text-center py-4">Empty stage</div>
        </div>

        <!-- Stage 4: Won -->
        <div class="bg-gray-100 rounded-lg p-3 border border-gray-200">
            <div class="flex items-center justify-between font-bold text-xs uppercase text-gray-600 mb-3">
                <span>Won</span>
                <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-700">
                    {{ $leads->where('stage', 'Won')->count() }}
                </span>
            </div>
            <div class="text-xs text-gray-400 italic text-center py-4">Empty stage</div>
        </div>
    </div>

    <!-- Create New CRM Lead Form (Interactive Addition) -->
    <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
        <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
            <i class="fa-solid fa-bullseye text-indigo-600 mr-2"></i>
            Tambah Prospek / Lead Baru (Pipeline Input)
        </h3>
        <form action="{{ route('s04.create') }}" method="POST" class="bg-gray-50 p-4 rounded-lg border text-xs grid grid-cols-1 sm:grid-cols-3 gap-3">
            @csrf
            <div>
                <label class="block font-medium text-gray-600 mb-1">Pelanggan Terkait</label>
                <select name="customer_id" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->id }} - {{ $c->name }} ({{ $c->city }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-medium text-gray-600 mb-1">Nama Prospek / Kesempatan</label>
                <input type="text" name="lead_name" placeholder="Pemesanan Skincare Corporate / Salon" class="w-full border rounded p-1.5 bg-white focus:outline-none">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-1.5 px-3 rounded shadow transition">
                    <i class="fa-solid fa-plus mr-1"></i> Buat Lead (New)
                </button>
            </div>
        </form>
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
