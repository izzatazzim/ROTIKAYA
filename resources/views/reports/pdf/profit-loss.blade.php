@extends('reports.pdf.layout')

@section('content')
    <div class="pdf-section-label">Revenue</div>
    <div class="pdf-gap-6">&nbsp;</div>
    <table class="pdf-kv">
        <tr class="is-last">
            <td class="label">Total Sales Revenue</td>
            <td class="value">{{ number_format($total_revenue, 2) }}</td>
        </tr>
    </table>

    <div class="pdf-gap-24">&nbsp;</div>

    <div class="pdf-section-label">Collections</div>
    <div class="pdf-gap-6">&nbsp;</div>
    <table class="pdf-kv">
        <tr class="is-last">
            <td class="label">Total Payments Received</td>
            <td class="value">{{ number_format($total_received, 2) }}</td>
        </tr>
    </table>

    <div class="pdf-gap-24">&nbsp;</div>

    <table class="pdf-totals">
        <tr class="grand">
            <td class="label">Outstanding Revenue</td>
            <td class="value">RM {{ number_format($total_revenue - $total_received, 2) }}</td>
        </tr>
    </table>
@endsection
