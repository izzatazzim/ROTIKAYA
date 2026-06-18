@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-white">Reports</h1>
        <p class="text-sm text-gray-500 mt-1">Generate financial and sales reports</p>
    </div>

    <div class="mb-6 bg-[#141414] border border-white/[0.06] rounded-xl p-6">
        <div class="flex items-start justify-between gap-6">
            <div>
                <h2 class="text-base font-semibold text-white mb-1">Customer Statement</h2>
                <p class="text-sm text-gray-400">A complete statement of all invoices and payments for one customer over a date range.</p>
            </div>
            <a href="{{ route('reports.client-statement') }}"
               class="shrink-0 inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-5 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export PDF
            </a>
        </div>
    </div>

    <!-- Financial Reports Section -->
    <div class="bg-[#141414] border border-white/[0.06] rounded-xl p-6 mb-6">
        <h2 class="text-base font-semibold text-white mb-4">Financial Reports</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <a href="{{ route('reports.download', 'balance-sheet') }}"
               class="flex items-center justify-between p-4 rounded-xl border border-white/[0.06] bg-[#0a0a0a] hover:bg-[#1a1a1a] transition group">
                <span class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-white font-medium">Balance Sheet</span>
                </span>
                <svg class="w-5 h-5 text-gray-500 group-hover:text-red-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.download', 'profit-loss') }}"
               class="flex items-center justify-between p-4 rounded-xl border border-white/[0.06] bg-[#0a0a0a] hover:bg-[#1a1a1a] transition group">
                <span class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-white font-medium">Profit & Loss</span>
                </span>
                <svg class="w-5 h-5 text-gray-500 group-hover:text-red-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.download', 'cash-flow') }}"
               class="flex items-center justify-between p-4 rounded-xl border border-white/[0.06] bg-[#0a0a0a] hover:bg-[#1a1a1a] transition group">
                <span class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-white font-medium">Cash Flow Statement</span>
                </span>
                <svg class="w-5 h-5 text-gray-500 group-hover:text-red-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.download', 'financial-summary') }}"
               class="flex items-center justify-between p-4 rounded-xl border border-white/[0.06] bg-[#0a0a0a] hover:bg-[#1a1a1a] transition group">
                <span class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-white font-medium">Financial Summary</span>
                </span>
                <svg class="w-5 h-5 text-gray-500 group-hover:text-red-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Sales Reports Section (Collapsible) -->
    <div class="mb-6" x-data="{ open: false }">
        <button @click="open = !open" class="w-full flex items-center justify-between text-base font-semibold text-white mb-2 hover:text-gray-300 transition">
            <span>Sales Reports</span>
            <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open" x-transition class="bg-[#141414] border border-white/[0.06] rounded-xl p-6">
            <p class="text-gray-400">Sales reports coming soon...</p>
        </div>
    </div>

    <!-- Payment Reports Section -->
    <div class="bg-[#141414] border border-white/[0.06] rounded-xl p-6 mb-6">
        <h2 class="text-base font-semibold text-white mb-4">Payment Reports</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <a href="{{ route('reports.download', 'receipt-summary') }}"
               class="flex items-center justify-between p-4 rounded-xl border border-white/[0.06] bg-[#0a0a0a] hover:bg-[#1a1a1a] transition group">
                <span class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-white font-medium">Receipt Summary</span>
                </span>
                <svg class="w-5 h-5 text-gray-500 group-hover:text-red-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.download', 'payment-voucher') }}"
               class="flex items-center justify-between p-4 rounded-xl border border-white/[0.06] bg-[#0a0a0a] hover:bg-[#1a1a1a] transition group">
                <span class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-white font-medium">Payment Voucher Summary</span>
                </span>
                <svg class="w-5 h-5 text-gray-500 group-hover:text-red-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Revenue Report Filter & Table -->
    <div class="bg-[#141414] border border-white/[0.06] rounded-xl p-6 mb-6">
        <h2 class="text-base font-semibold text-white mb-4">Revenue Report</h2>
        <form method="GET" action="{{ route('reports.index') }}" class="mb-6" id="revenue-report-filters">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                           class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}"
                           class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Customer</label>
                    <select name="client_id"
                            class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500">
                        <option value="">All Customers</option>
                        @foreach ($clients ?? [] as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Sales Rep</label>
                    <select name="salesperson_id"
                            class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500">
                        <option value="">All Sales Reps</option>
                        @foreach ($salespeople ?? [] as $person)
                            <option value="{{ $person->id }}" {{ request('salesperson_id') == $person->id ? 'selected' : '' }}>{{ $person->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <div class="mb-6 flex flex-col sm:flex-row sm:flex-wrap gap-3">
            <button type="submit" form="revenue-report-filters" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-6 rounded-lg transition">
                Generate Report
            </button>

            <form method="POST" action="{{ route('reports.revenue.export-pdf') }}">
                @csrf
                <input type="hidden" name="from_date" value="{{ request('from_date') }}">
                <input type="hidden" name="to_date" value="{{ request('to_date') }}">
                <input type="hidden" name="client_id" value="{{ request('client_id') }}">
                <input type="hidden" name="campaign_name" value="{{ request('campaign_name') }}">
                <button type="submit" class="w-full sm:w-auto rounded-lg border border-white/10 bg-white/5 px-6 py-2.5 text-sm font-medium text-gray-300 transition-colors hover:bg-white/10">
                    Export PDF
                </button>
            </form>

            <form method="POST" action="{{ route('reports.revenue.export-xlsx') }}">
                @csrf
                <input type="hidden" name="from_date" value="{{ request('from_date') }}">
                <input type="hidden" name="to_date" value="{{ request('to_date') }}">
                <input type="hidden" name="client_id" value="{{ request('client_id') }}">
                <input type="hidden" name="campaign_name" value="{{ request('campaign_name') }}">
                <button type="submit" class="w-full sm:w-auto rounded-lg border border-white/10 bg-white/5 px-6 py-2.5 text-sm font-medium text-gray-300 transition-colors hover:bg-white/10">
                    Export XLSX
                </button>
            </form>
        </div>

        <!-- Total Revenue -->
        <div class="mb-4 p-4 bg-white/[0.02] border border-white/10 rounded-xl">
            <span class="text-gray-400 text-sm">Total Revenue:</span>
            <span class="ml-2 text-2xl font-mono font-bold text-white">RM {{ number_format($total, 2) }}</span>
        </div>

        <!-- Sales Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Sales Rep</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr class="border-b border-white/[0.04] hover:bg-white/[0.02] transition">
                            <td class="px-4 py-3 font-mono text-xs text-gray-300">{{ $sale->created_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-white">{{ $sale->client->name ?? 'Unknown' }}</td>
                            <td class="px-4 py-3 text-white">{{ $sale->salesperson->name ?? 'Unknown' }}</td>
                            <td class="px-4 py-3 font-mono text-white">RM{{ number_format($sale->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8">
                                <x-empty-state
                                    title="No records found"
                                    message="Try adjusting your report filters or date range."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
