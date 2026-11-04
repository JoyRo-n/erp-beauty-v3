@extends('layouts.odoo')

@section('title', 'S-02 Form Customer dan Credit Terms')
@section('breadcrumb', 'Sales > Customers > ' . ($selectedCustomer->name ?? 'Customer'))

@section('content')
<div class="space-y-4">
    <!-- Control Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-700 text-sm">Customers</span>
            <span class="text-gray-400">|</span>
            <span class="text-xs text-gray-500">Views: <strong>Form</strong> &bull; List</span>
        </div>
        <div class="flex items-center space-x-3">
            <div class="text-xs text-gray-500">
                Total Opening AR Perusahaan: <strong class="text-purple-700 font-bold">Rp{{ number_format($totalOpeningAr, 0, ',', '.') }}</strong>
            </div>
            <!-- Customer Selector -->
            <form method="GET" action="{{ route('s02.customer') }}">
                <select name="id" onchange="this.form.submit()" class="text-xs border rounded p-1.5 bg-gray-50 focus:outline-none">
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ $selectedCustomer->id === $c->id ? 'selected' : '' }}>
                            {{ $c->id }} — {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Main Customer Form (Matching S-02 Wireframe) -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <div class="flex justify-between items-start border-b pb-4 mb-4">
            <div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-purple-100 text-purple-700">Customer Profile</span>
                <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $selectedCustomer->id }} — {{ $selectedCustomer->name }}</h2>
                <div class="text-xs text-gray-500">{{ $selectedCustomer->type }} &bull; {{ $selectedCustomer->city }}</div>
            </div>
            <!-- Credit Exposure Metric Card (DL-05) -->
            <div class="text-right bg-purple-50 border border-purple-200 rounded-lg p-3">
                <div class="text-xs text-gray-500">Calculated Credit Exposure</div>
                <div class="text-xl font-bold {{ $exposure > $selectedCustomer->credit_limit ? 'text-rose-600' : 'text-purple-700' }}">
                    Rp{{ number_format($exposure, 0, ',', '.') }}
                </div>
                <div class="text-xs {{ $exposure > $selectedCustomer->credit_limit ? 'text-rose-600 font-bold' : 'text-gray-500' }}">
                    Limit: Rp{{ number_format($selectedCustomer->credit_limit, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Customer ID</label>
                <div class="p-2 bg-gray-50 rounded border text-gray-800 font-mono">{{ $selectedCustomer->id }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Credit Limit (SR-08)</label>
                <div class="p-2 bg-gray-50 rounded border text-gray-800 font-bold">Rp{{ number_format($selectedCustomer->credit_limit, 0, ',', '.') }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Opening Unpaid AR</label>
                <div class="p-2 bg-gray-50 rounded border text-gray-800">Rp{{ number_format($selectedCustomer->opening_receivable, 0, ',', '.') }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Payment Terms</label>
                <div class="p-2 bg-gray-50 rounded border text-gray-800">{{ $selectedCustomer->payment_terms }}</div>
            </div>
        </div>

        <!-- All Customers Table (S-02 Field Inti) -->
        <div class="mt-8">
            <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Customer List & Balances</h3>
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-2.5">Contact ID</th>
                            <th class="p-2.5">Name</th>
                            <th class="p-2.5">Type</th>
                            <th class="p-2.5">City</th>
                            <th class="p-2.5">Payment Terms</th>
                            <th class="p-2.5 text-right">Credit Limit</th>
                            <th class="p-2.5 text-right">Opening AR</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($customers as $c)
                            <tr class="hover:bg-purple-50/50 {{ $c->id === $selectedCustomer->id ? 'bg-purple-50 font-medium' : '' }}">
                                <td class="p-2.5 font-mono text-purple-700">
                                    <a href="{{ route('s02.customer', ['id' => $c->id]) }}">{{ $c->id }}</a>
                                </td>
                                <td class="p-2.5">{{ $c->name }}</td>
                                <td class="p-2.5">{{ $c->type }}</td>
                                <td class="p-2.5">{{ $c->city }}</td>
                                <td class="p-2.5">{{ $c->payment_terms }}</td>
                                <td class="p-2.5 text-right font-semibold">Rp{{ number_format($c->credit_limit, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right">Rp{{ number_format($c->opening_receivable, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create New Customer Form (Interactive Addition) -->
        <div class="mt-8 border-t pt-6">
            <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                <i class="fa-solid fa-user-plus text-purple-600 mr-2"></i>
                Tambah Kontak Pelanggan Baru (Master Customer)
            </h3>
            <form action="{{ route('s02.create') }}" method="POST" class="bg-gray-50 p-4 rounded-lg border text-xs grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                @csrf
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Nama Pelanggan</label>
                    <input type="text" name="name" required placeholder="Contoh: Bunga Melati" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Tipe Kontak</label>
                    <select name="type" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                        <option value="Perorangan">Perorangan</option>
                        <option value="Perusahaan">Perusahaan</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Kota</label>
                    <input type="text" name="city" required placeholder="Bandung" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Batas Kredit (Credit Limit)</label>
                    <input type="number" name="credit_limit" required value="2000000" min="0" step="100000" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block font-medium text-gray-600 mb-1">Payment Terms</label>
                    <div class="flex space-x-2">
                        <select name="payment_terms" class="w-full border rounded p-1.5 bg-white focus:outline-none">
                            <option value="Net 14">Net 14</option>
                            <option value="Net 30">Net 30</option>
                            <option value="Immediate">Immediate</option>
                        </select>
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-1.5 px-3 rounded shadow transition whitespace-nowrap">
                            <i class="fa-solid fa-plus mr-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Chatter -->
    @include('partials.odoo_chatter')
</div>
@endsection
