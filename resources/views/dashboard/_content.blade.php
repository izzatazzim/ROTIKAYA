{{-- Collapsible filter bar --}}
<div x-data="{ showFilters: false }" class="mb-6 rounded-xl border border-white/[0.06] bg-[#141414]">
    {{-- Mobile toggle --}}
    <button @click="showFilters = !showFilters" type="button"
            class="md:hidden w-full flex items-center justify-between px-5 py-4 text-sm font-medium text-white">
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Filters
        </span>
        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showFilters }"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Form: always visible md+, toggled on mobile --}}
    <div :class="{ 'hidden': !showFilters }" class="p-6 md:!block">
        <form method="GET" action="{{ route('dashboard') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-5">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-400">Start Date</label>
                    <input
                        type="date"
                        name="start"
                        value="{{ $filters['start'] }}"
                        class="w-full rounded-lg border border-white/10 bg-white/[0.02] px-3.5 py-2.5 text-sm text-white focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500/30"
                    >
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-400">End Date</label>
                    <input
                        type="date"
                        name="end"
                        value="{{ $filters['end'] }}"
                        class="w-full rounded-lg border border-white/10 bg-white/[0.02] px-3.5 py-2.5 text-sm text-white focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500/30"
                    >
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-400">Customer</label>
                    <select
                        name="client_id"
                        class="w-full rounded-lg border border-white/10 bg-white/[0.02] px-3.5 py-2.5 text-sm text-white focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500/30"
                    >
                        <option value="">All Customers</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected((string) $filters['client_id'] === (string) $client->id)>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if ($showSalespersonFilter)
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-400">Sales Rep</label>
                        <select
                            name="salesperson_id"
                            class="w-full rounded-lg border border-white/10 bg-white/[0.02] px-3.5 py-2.5 text-sm text-white focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500/30"
                        >
                            <option value="">All Sales Reps</option>
                            @foreach ($salespeople as $salesperson)
                                <option value="{{ $salesperson->id }}" @selected((string) $filters['salesperson_id'] === (string) $salesperson->id)>
                                    {{ $salesperson->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="flex items-end gap-2 {{ $showSalespersonFilter ? '' : 'sm:col-span-2 md:col-span-2' }}">
                    <button
                        type="submit"
                        class="w-full sm:w-auto rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-red-700"
                    >
                        Apply Filters
                    </button>
                    <a
                        href="{{ route('dashboard') }}"
                        class="w-full sm:w-auto text-center rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-gray-300 transition-colors hover:bg-white/10"
                    >
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Stat cards: 1 col → 2 col sm → 4 col lg --}}
<div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Revenue card -->
    <div class="rounded-xl border border-white/[0.06] bg-[#141414] p-6 shadow-inner-light">
        <div class="flex items-start justify-between">
            <span class="flex items-center gap-1.5 text-xs text-gray-500">
                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                {{ $isDefaultRange ? 'Revenue This Month' : 'Revenue' }}
            </span>
            <svg class="w-5 h-5 text-gray-500 opacity-40 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="mt-3 text-3xl font-mono font-semibold tracking-tight text-white">RM{{ number_format((float) $stats['revenue'], 2) }}</div>
    </div>

    <!-- Overdue % card -->
    <div class="rounded-xl border border-white/[0.06] bg-[#141414] p-6 shadow-inner-light">
        <div class="flex items-start justify-between">
            <span class="text-xs text-gray-500">Overdue Invoices</span>
            <svg class="w-5 h-5 text-gray-500 opacity-40 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="mt-3 text-3xl font-mono font-semibold tracking-tight text-white">{{ $stats['overdue_percentage'] }}%</div>
    </div>

    <!-- Active customers card -->
    <div class="rounded-xl border border-white/[0.06] bg-[#141414] p-6 shadow-inner-light">
        <div class="flex items-start justify-between">
            <span class="text-xs text-gray-500">Active Customers</span>
            <svg class="w-5 h-5 text-gray-500 opacity-40 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div class="mt-3 text-3xl font-mono font-semibold tracking-tight text-white">{{ $stats['active_clients'] }}</div>
    </div>

    <!-- New payments card -->
    <div class="rounded-xl border border-white/[0.06] bg-[#141414] p-6 shadow-inner-light">
        <div class="flex items-start justify-between">
            <span class="text-xs text-gray-500">Payments Received</span>
            <svg class="w-5 h-5 text-gray-500 opacity-40 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="mt-3 text-3xl font-mono font-semibold tracking-tight text-white">{{ $stats['new_payments'] }}</div>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="rounded-xl border border-white/[0.06] bg-[#141414] p-6 shadow-inner-light">
        <h2 class="text-base font-semibold text-white">Invoices</h2>
        <p class="mb-4 mt-1 text-xs text-gray-500">
            {{ ucfirst($chartData['granularity'] ?? 'daily') }} trend
        </p>

        @if (count($chartData['labels'] ?? []) > 0 && array_sum($chartData['values'] ?? []) > 0)
            <div class="h-48 sm:h-64 lg:h-72">
                <canvas id="invoicesChart"></canvas>
            </div>
        @else
            <x-empty-state
                title="No data for this period"
                message="No revenue data for this period yet."
            />
        @endif

        <p class="mt-4 text-xs text-gray-500">
            Overdue: <span class="font-mono text-base font-semibold text-white">{{ $stats['overdue_invoices'] }}</span> invoice{{ $stats['overdue_invoices'] === 1 ? '' : 's' }} unpaid past due date
        </p>
    </div>

    <div class="rounded-xl border border-white/[0.06] bg-[#141414] p-6 shadow-inner-light">
        <h2 class="mb-4 text-base font-semibold text-white">Latest Payments</h2>
        <div class="space-y-1">
            @forelse ($recentPayments as $payment)
                @php
                    $payerName = $payment->invoice?->sale?->client?->name ?? 'Unknown Customer';
                    $payerInitial = strtoupper(substr(trim($payerName), 0, 1) ?: 'U');
                @endphp
                <div class="flex items-center gap-3 py-2.5">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/[0.06] text-xs font-medium text-gray-300">
                        {{ $payerInitial }}
                    </div>
                    <span class="truncate text-sm text-gray-200" title="{{ $payerName }}">{{ $payerName }}</span>
                    <span class="ml-auto shrink-0 text-right">
                        <span class="font-mono text-sm font-medium text-white">RM{{ number_format((float) $payment->amount, 2) }}</span>
                        <span class="ml-2 text-xs text-gray-500">{{ optional($payment->payment_date)->format('M d') }}</span>
                    </span>
                </div>
            @empty
                <x-empty-state
                    title="No payments found"
                    message="No payments match the selected filters."
                />
            @endforelse
        </div>
    </div>
</div>

@if (count($chartData['labels'] ?? []) > 0 && array_sum($chartData['values'] ?? []) > 0)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('invoicesChart');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartData['labels'] ?? []),
                    datasets: [{
                        label: 'Invoices',
                        data: @json($chartData['values'] ?? []),
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#dc2626',
                        pointBorderColor: '#dc2626',
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: {
                                color: '#6b6b6b',
                                font: { size: 11 },
                                maxTicksLimit: 8,
                                maxRotation: 0
                            }
                        },
                        y: {
                            display: false,
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
    @endpush
@endif
