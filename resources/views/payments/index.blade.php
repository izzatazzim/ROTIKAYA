@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-white">Payments</h1>
        <p class="text-sm text-gray-500 mt-1">Record and track customer payments</p>
    </div>

    <!-- Record Payment Form -->
    <div class="bg-[#141414] border border-white/[0.06] rounded-xl p-6 mb-6">
        <h2 class="text-base font-semibold text-white mb-4">Record New Payment</h2>
        <form method="POST" action="{{ route('payments.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Invoice</label>
                    <select name="invoice_id" required
                            class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500">
                        <option value="">Select an invoice...</option>
                        @foreach ($invoices as $invoice)
                            <option value="{{ $invoice->id }}">
                                {{ $invoice->invoice_number }} — {{ $invoice->sale->client->name ?? 'Unknown' }} (RM{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }} remaining)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Amount</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-mono text-sm">RM</span>
                        <input name="amount" type="number" step="0.01" placeholder="0.00" required
                               class="w-full bg-white/[0.02] border border-white/10 rounded-lg pl-12 pr-4 py-2.5 text-white font-mono focus:outline-none focus:border-red-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Payment Date</label>
                    <input name="payment_date" type="date" required
                           class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Payment Method</label>
                    <select name="method"
                            class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="online">Online Payment</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Reference Number</label>
                    <input name="reference" placeholder="Transaction reference"
                           class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Notes</label>
                    <input name="notes" placeholder="Optional notes"
                           class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
                </div>
            </div>
            <button type="submit" class="mt-4 w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-6 rounded-lg transition">
                Record Payment
            </button>
        </form>
    </div>

    <!-- Payments Table — Strategy A: horizontal scroll -->
    <div class="bg-[#141414] border border-white/[0.06] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width: 560px;">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hidden sm:table-cell">Method</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hidden md:table-cell">Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr class="border-b border-white/[0.04] hover:bg-white/[0.02] transition">
                            <td class="px-4 py-3 font-mono text-xs text-white whitespace-nowrap">{{ $payment->invoice->invoice_number }}</td>
                            <td class="px-4 py-3 text-white max-w-[140px]">
                                <span class="block truncate" title="{{ $payment->invoice->sale->client->name ?? '—' }}">
                                    {{ $payment->invoice->sale->client->name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-white whitespace-nowrap">RM{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-3 text-gray-300 hidden sm:table-cell">{{ ucfirst(str_replace('_', ' ', $payment->method ?? 'N/A')) }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-400 hidden md:table-cell">{{ $payment->reference ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-300 whitespace-nowrap">{{ $payment->payment_date->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">No payments recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if(method_exists($payments, 'links'))
        <div class="mt-4">{{ $payments->links() }}</div>
    @endif
@endsection
