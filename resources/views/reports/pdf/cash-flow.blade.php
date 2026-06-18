@extends('reports.pdf.layout')

@section('content')
    <table class="pdf-table">
        <thead>
            <tr>
                <th style="width:15%;">Date</th>
                <th style="width:33%;">Customer</th>
                <th style="width:22%;">Reference</th>
                <th style="width:14%;">Method</th>
                <th class="num" style="width:16%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                    <td>{{ $payment->invoice?->sale?->client?->company_name ?? $payment->invoice?->sale?->client?->name ?? 'Unknown' }}</td>
                    <td>{{ $payment->reference ?? '—' }}</td>
                    <td>{{ ucfirst($payment->method ?? 'Unknown') }}</td>
                    <td class="num">{{ number_format($payment->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td class="empty" colspan="5">No payments found for this period.</td></tr>
            @endforelse
        </tbody>
        @if($payments->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="4">Total Cash Inflow</td>
                    <td class="num">RM {{ number_format($total, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
