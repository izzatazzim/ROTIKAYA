@extends('reports.pdf.layout')

@section('content')
    @php
        $collectionRate = $total_sales > 0 ? ($total_payments / $total_sales) * 100 : 0;
    @endphp

    <div class="pdf-section-label">Sales &amp; Collections</div>
    <div class="pdf-gap-6">&nbsp;</div>
    <table class="pdf-kv">
        <tr>
            <td class="label">Total Sales Value</td>
            <td class="value">{{ number_format($total_sales, 2) }}</td>
        </tr>
        <tr class="is-last">
            <td class="label">Total Payments Received</td>
            <td class="value">{{ number_format($total_payments, 2) }}</td>
        </tr>
    </table>

    <div class="pdf-gap-24">&nbsp;</div>

    <div class="pdf-section-label">Invoices</div>
    <div class="pdf-gap-6">&nbsp;</div>
    <table class="pdf-kv">
        <tr>
            <td class="label">Total Invoices Generated</td>
            <td class="value">{{ number_format($total_invoices) }}</td>
        </tr>
        <tr class="is-last">
            <td class="label">Overdue Invoices</td>
            <td class="value {{ $overdue_invoices > 0 ? 'pdf-due' : '' }}">{{ number_format($overdue_invoices) }}</td>
        </tr>
    </table>

    <div class="pdf-gap-24">&nbsp;</div>

    <table class="pdf-totals">
        <tr class="grand">
            <td class="label">Collection Rate</td>
            <td class="value">{{ number_format($collectionRate, 1) }}%</td>
        </tr>
    </table>
@endsection
