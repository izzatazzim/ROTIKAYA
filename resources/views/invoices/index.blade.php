@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-white">Invoices</h1>
        <p class="text-sm text-gray-500 mt-1">Manage customer invoices and payments</p>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach (['all' => 'All', 'outstanding' => 'Awaiting Payment', 'coming_due' => 'Due Soon', 'overdue' => 'Overdue', 'partial' => 'Partially Paid', 'paid' => 'Paid'] as $key => $label)
            <a href="{{ route('invoices.index', array_merge(request()->query(), ['status' => $key])) }}"
               class="px-3 py-1.5 rounded-full text-xs font-medium transition {{ $status === $key ? 'bg-red-500/10 text-red-400 ring-1 ring-red-500/20' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- Filter Bar (collapsible on mobile) -->
    <div x-data="{ showFilters: false }" class="bg-[#141414] border border-white/[0.06] rounded-xl mb-6">
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
        <div :class="{ 'hidden': !showFilters }" class="p-6 md:!block">
            <form method="GET" action="{{ route('invoices.index') }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="flex flex-col gap-4 md:flex-row md:items-end">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 md:flex-1">
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Start Date</label>
                            <input type="date" name="from_date" value="{{ request('from_date') }}"
                                   class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">End Date</label>
                            <input type="date" name="to_date" value="{{ request('to_date') }}"
                                   class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice number..."
                                   class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Customer</label>
                            <input type="text" name="customer" value="{{ request('customer') }}" placeholder="Customer name..."
                                   class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
                        </div>
                    </div>
                    <button type="submit" class="w-full md:w-auto md:shrink-0 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-6 rounded-lg transition">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Sale / Create Invoice -->
    @if (auth()->user()?->hasRole('sales_staff'))
        <div class="mb-4">
            <a href="{{ route('sales.index') }}" class="inline-block bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-6 rounded-lg transition">
                + Add Sale
            </a>
        </div>
    @else
        <form method="POST" action="{{ route('invoices.store') }}" class="mb-6 flex flex-col sm:flex-row items-stretch sm:items-end gap-4">
            @csrf
            <div class="flex-1 sm:max-w-md">
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Create Invoice from Completed Sale</label>
                <select name="sale_id" required
                        class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500">
                    <option value="">Select a completed sale...</option>
                    @foreach ($completedSales as $sale)
                        <option value="{{ $sale->id }}">{{ $sale->campaign_name }} — {{ $sale->client->name ?? 'Unknown' }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-6 rounded-lg transition">
                Create Invoice
            </button>
        </form>
    @endif

    <!-- Pagination Info -->
    <div class="mb-2 text-sm text-gray-400">
        {{ $invoices->firstItem() ?? 0 }}-{{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }} items
    </div>

    <!-- Invoices Table — Strategy A: horizontal scroll with sticky first col -->
    <div class="bg-[#141414] border border-white/[0.06] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width: 640px;">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 w-8 sticky left-0 bg-[#141414]"></th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sticky left-8 bg-[#141414]">No.</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr class="border-b border-white/[0.04] hover:bg-white/[0.02] transition">
                            <td class="px-4 py-3 sticky left-0 bg-[#141414]">
                                @php
                                    $dotColor = match($invoice->status) {
                                        'paid' => 'bg-emerald-400',
                                        'overdue' => 'bg-red-400',
                                        'partial' => 'bg-amber-400',
                                        'coming_due' => 'bg-blue-400',
                                        default => 'bg-gray-400',
                                    };
                                @endphp
                                <span class="inline-block h-2 w-2 rounded-full {{ $dotColor }}"></span>
                            </td>
                            <td class="px-4 py-3 sticky left-8 bg-[#141414]">
                                <span class="font-mono text-xs text-white">{{ $invoice->invoice_number }}</span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-300 whitespace-nowrap">{{ $invoice->due_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-white max-w-[160px]">
                                <span class="block truncate" title="{{ $invoice->sale->client->name ?? 'Unknown' }}">
                                    {{ $invoice->sale->client->name ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-white whitespace-nowrap">RM{{ number_format($invoice->total_amount, 2) }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $badgeConfig = match($invoice->status) {
                                        'paid'       => ['bg-emerald-500/10 text-emerald-400 border border-emerald-500/20', 'Paid'],
                                        'overdue'    => ['bg-red-500/10 text-red-400 border border-red-500/20', 'Overdue'],
                                        'partial'    => ['bg-amber-500/10 text-amber-400 border border-amber-500/20', 'Partial'],
                                        'coming_due' => ['bg-blue-500/10 text-blue-400 border border-blue-500/20', 'Due Soon'],
                                        default      => ['bg-gray-500/10 text-gray-400 border border-gray-500/20', 'Awaiting'],
                                    };
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $badgeConfig[0] }}">
                                    {{ $badgeConfig[1] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-sm text-gray-400 hover:text-white hover:underline transition whitespace-nowrap">View</a>
                                    @if (auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('accountant'))
                                        <div
                                            x-data="invoiceSendButton({
                                                endpoint: '{{ route('invoices.send', $invoice) }}',
                                                csrf: '{{ csrf_token() }}',
                                                lastSent: '{{ optional($invoice->lastSuccessfulDispatch?->dispatched_at)->format('Y-m-d H:i:s') }}'
                                            })"
                                            class="flex items-center gap-2"
                                        >
                                            <button
                                                type="button"
                                                @click="sendInvoice"
                                                :disabled="state === 'loading'"
                                                :title="errorMessage"
                                                class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-semibold transition whitespace-nowrap"
                                                :class="buttonClass"
                                            >
                                                <template x-if="state === 'loading'">
                                                    <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="4"></circle>
                                                        <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"></path>
                                                    </svg>
                                                </template>
                                                <span x-text="buttonText"></span>
                                            </button>
                                            <span class="text-xs text-gray-400 hidden sm:inline" x-show="lastSentLabel" x-text="'Sent: ' + lastSentLabel"></span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8">
                                <x-empty-state
                                    title="No invoices found"
                                    message="No invoices match the current filters."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-sm">
        <div class="flex items-center gap-2">
            @if ($invoices->previousPageUrl())
                <a href="{{ $invoices->previousPageUrl() }}" class="px-3 py-2 rounded bg-white/5 text-white hover:bg-white/10 transition">&lt;</a>
            @endif

            @for ($i = 1; $i <= min($invoices->lastPage(), 4); $i++)
                <a href="{{ $invoices->url($i) }}"
                   class="px-3 py-2 rounded-lg transition {{ $invoices->currentPage() === $i ? 'bg-red-600 text-white' : 'bg-white/5 text-white hover:bg-white/10' }}">
                    {{ $i }}
                </a>
            @endfor

            @if ($invoices->nextPageUrl())
                <a href="{{ $invoices->nextPageUrl() }}" class="px-3 py-2 rounded bg-white/5 text-white hover:bg-white/10 transition">&gt;</a>
            @endif
        </div>
        <div class="text-gray-400">
            {{ $invoices->perPage() }}/page
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function invoiceSendButton({ endpoint, csrf, lastSent }) {
        return {
            endpoint,
            csrf,
            state: 'default',
            errorMessage: '',
            lastSentLabel: lastSent || '',
            get buttonClass() {
                if (this.state === 'sent') return 'bg-emerald-700 hover:bg-emerald-600 text-white';
                if (this.state === 'failed') return 'bg-red-600 hover:bg-red-700 text-white';
                return 'bg-red-600 hover:bg-red-700 text-white';
            },
            get buttonText() {
                if (this.state === 'loading') return 'Sending...';
                if (this.state === 'sent') return 'Sent ✓';
                if (this.state === 'failed') return 'Failed — Retry';
                return 'Send';
            },
            async sendInvoice() {
                this.state = 'loading';
                this.errorMessage = '';

                try {
                    const response = await fetch(this.endpoint, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                    });
                    const payload = await response.json();

                    if (!response.ok || !payload.ok) {
                        this.state = 'failed';
                        this.errorMessage = payload.error || 'Failed to send invoice.';
                        return;
                    }

                    this.state = 'sent';
                    this.lastSentLabel = payload.last_sent_at ?? this.lastSentLabel;
                } catch (error) {
                    this.state = 'failed';
                    this.errorMessage = 'Unable to send invoice right now.';
                }
            }
        };
    }
</script>
@endpush
