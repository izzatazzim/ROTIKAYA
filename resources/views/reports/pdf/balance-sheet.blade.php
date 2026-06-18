@extends('reports.pdf.layout')

@section('content')
    <div class="pdf-section-label">Assets</div>
    <div class="pdf-gap-6">&nbsp;</div>
    <table class="pdf-kv">
        <tr class="is-last">
            <td class="label">Accounts Receivable (Outstanding Invoices)</td>
            <td class="value">{{ number_format($total_receivables, 2) }}</td>
        </tr>
    </table>

    <div class="pdf-gap-24">&nbsp;</div>

    <div class="pdf-section-label">Cash Position</div>
    <div class="pdf-gap-6">&nbsp;</div>
    <table class="pdf-kv">
        <tr class="is-last">
            <td class="label">Total Payments Received</td>
            <td class="value">{{ number_format($total_paid, 2) }}</td>
        </tr>
    </table>

    <div class="pdf-gap-24">&nbsp;</div>

    <table class="pdf-totals">
        <tr class="grand">
            <td class="label">Net Position</td>
            <td class="value {{ $net_position < 0 ? 'pdf-due' : '' }}">RM {{ number_format($net_position, 2) }}</td>
        </tr>
    </table>
@endsection
