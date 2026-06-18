@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <a href="{{ route('invoices.index') }}" class="text-sm text-gray-400 hover:text-white transition">← Back to invoices</a>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white">Invoice {{ $invoice->invoice_number }}</h1>
            <p class="text-sm text-gray-400">
                Last sent:
                {{ $invoice->lastSuccessfulDispatch?->dispatched_at?->format('Y-m-d H:i:s') ?? 'Not sent yet' }}
            </p>
        </div>

        @if (auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('accountant'))
            <div
                x-data="invoiceSendButton({
                    endpoint: '{{ route('invoices.send', $invoice) }}',
                    csrf: '{{ csrf_token() }}',
                    lastSent: '{{ optional($invoice->lastSuccessfulDispatch?->dispatched_at)->format('Y-m-d H:i:s') }}'
                })"
                class="flex flex-wrap items-center gap-3"
            >
                <button
                    type="button"
                    @click="sendInvoice"
                    :disabled="state === 'loading'"
                    :title="errorMessage"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition"
                    :class="buttonClass"
                >
                    <template x-if="state === 'loading'">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="4"></circle>
                            <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"></path>
                        </svg>
                    </template>
                    <span x-text="buttonText"></span>
                </button>
                <span class="text-xs text-gray-400" x-show="lastSentLabel" x-text="'Last sent: ' + lastSentLabel"></span>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-xl border border-white/[0.06] bg-[#141414] p-5">
            <h2 class="mb-4 text-lg font-semibold text-white">Invoice Details</h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Customer</dt>
                    <dd class="text-white truncate" title="{{ $invoice->sale?->client?->name ?? '-' }}">{{ $invoice->sale?->client?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Campaign</dt>
                    <dd class="text-white truncate" title="{{ $invoice->sale?->campaign_name ?? '-' }}">{{ $invoice->sale?->campaign_name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Issue Date</dt>
                    <dd class="text-white">{{ optional($invoice->issue_date)->format('Y-m-d') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Due Date</dt>
                    <dd class="text-white">{{ optional($invoice->due_date)->format('Y-m-d') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Total</dt>
                    <dd class="text-white font-mono">RM{{ number_format((float) $invoice->total_amount, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Paid</dt>
                    <dd class="text-white font-mono">RM{{ number_format((float) $invoice->paid_amount, 2) }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-white/[0.06] bg-[#141414] p-5">
            <h2 class="mb-4 text-lg font-semibold text-white">Send History</h2>
            <div class="space-y-3">
                @forelse ($invoice->dispatches->sortByDesc('dispatched_at')->take(5) as $dispatch)
                    <div class="rounded-lg border border-white/[0.06] bg-[#141414] p-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-white capitalize">{{ $dispatch->channel }}</span>
                            @if ($dispatch->status === 'sent')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Delivered</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20">Failed</span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-gray-400 truncate" title="{{ $dispatch->recipient }}">{{ $dispatch->recipient }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ $dispatch->dispatched_at?->format('d M Y, H:i') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">This invoice hasn't been sent yet.</p>
                @endforelse
            </div>
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
                return 'Send Invoice';
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
