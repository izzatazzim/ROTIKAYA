@extends('reports.pdf.layout')

@section('content')
    <table class="pdf-table">
        <thead>
            <tr>
                <th style="width:14%;">Date</th>
                <th style="width:30%;">Customer</th>
                <th style="width:20%;">Invoice No.</th>
                <th style="width:20%;">Reference</th>
                <th class="num" style="width:16%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                    <td>{{ $payment->invoice?->sale?->client?->company_name ?? $payment->invoice?->sale?->client?->name ?? 'Unknown' }}</td>
                    <td>{{ $payment->invoice?->invoice_number ?? '—' }}</td>
                    <td>{{ $payment->reference ?? '—' }}</td>
                    <td class="num">{{ number_format($payment->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td class="empty" colspan="5">No receipts found for this period.</td></tr>
            @endforelse
        </tbody>
        @if($payments->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="4">Total Receipts</td>
                    <td class="num">RM {{ number_format($total, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
