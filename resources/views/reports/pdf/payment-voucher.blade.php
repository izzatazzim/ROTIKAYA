@extends('reports.pdf.layout')

@section('content')
    <table class="pdf-table">
        <thead>
            <tr>
                <th style="width:18%;">Invoice No.</th>
                <th style="width:26%;">Customer</th>
                <th style="width:13%;">Issue Date</th>
                <th style="width:13%;">Due Date</th>
                <th class="num" style="width:15%;">Total</th>
                <th class="num" style="width:15%;">Paid</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->sale?->client?->company_name ?? $invoice->sale?->client?->name ?? 'Unknown' }}</td>
                    <td>{{ $invoice->issue_date->format('d M Y') }}</td>
                    <td>{{ $invoice->due_date->format('d M Y') }}</td>
                    <td class="num">{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="num">{{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td class="empty" colspan="6">No invoices found for this period.</td></tr>
            @endforelse
        </tbody>
        @if($invoices->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="4">Totals</td>
                    <td class="num">RM {{ number_format($invoices->sum('total_amount'), 2) }}</td>
                    <td class="num">RM {{ number_format($invoices->sum('paid_amount'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
