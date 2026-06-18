<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    @include('pdf._styles')
</head>
<body>

@php
    $client = $invoice->sale?->client;
    $sale   = $invoice->sale;
    $amt    = (float) $invoice->total_amount;
    $paid   = (float) $invoice->paid_amount;
    $bal    = $amt - $paid;

    $termDays = ($invoice->issue_date && $invoice->due_date)
        ? $invoice->issue_date->diffInDays($invoice->due_date)
        : 30;
@endphp

@include('pdf._header', [
    'docTitle' => 'Invoice',
    'metaRows' => [
        'No.'          => $invoice->invoice_number,
        'Date'         => optional($invoice->issue_date)->format('d M Y'),
        'Payment Term' => 'NET ' . $termDays,
        'Due Date'     => optional($invoice->due_date)->format('d M Y'),
    ],
])

{{-- BILL TO --}}
<div class="pdf-section-label">Bill To</div>
<div class="pdf-gap-6">&nbsp;</div>
<div class="pdf-name">{{ $client?->company_name ?? $client?->name ?? 'Unknown Customer' }}</div>
<div class="pdf-body">{{ $client?->address ?? 'Malaysia' }}</div>
@if($client?->phone)
    <div class="pdf-body">{{ $client->phone }}</div>
@endif

<div class="pdf-gap-24">&nbsp;</div>

{{-- LINE ITEMS --}}
<table class="pdf-table">
    <thead>
        <tr>
            <th style="width:46%;">Description</th>
            <th class="num" style="width:8%;">Qty</th>
            <th class="num" style="width:15%;">Unit Price</th>
            <th class="num" style="width:9%;">Disc</th>
            <th class="num" style="width:22%;">Net Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr class="is-last">
            <td>
                <div style="color:#1a1a1a; font-weight:700;">Rotikaya Advertising Campaign</div>
                <div>{{ $sale?->campaign_name ?? 'Advertising Services' }}</div>
                @if($sale?->start_date && $sale?->end_date)
                    <div class="pdf-small">{{ $sale->start_date->format('d M Y') }} &ndash; {{ $sale->end_date->format('d M Y') }}</div>
                @endif
            </td>
            <td class="num">1</td>
            <td class="num">{{ number_format($amt, 2) }}</td>
            <td class="num">0.00</td>
            <td class="num">{{ number_format($amt, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="pdf-gap-12">&nbsp;</div>

{{-- TOTALS --}}
<table class="pdf-totals">
    <tr class="subtotal">
        <td class="label">Subtotal</td>
        <td class="value">{{ number_format($amt, 2) }}</td>
    </tr>
    <tr class="grand">
        <td class="label">Total</td>
        <td class="value">RM {{ number_format($amt, 2) }}</td>
    </tr>
    @if($paid > 0)
        <tr>
            <td class="label">Amount Paid</td>
            <td class="value">{{ number_format($paid, 2) }}</td>
        </tr>
        <tr>
            <td class="label" style="font-weight:700; color:#1a1a1a;">Balance Due</td>
            <td class="value {{ $bal > 0 ? 'pdf-due' : '' }}" style="font-weight:700;">RM {{ number_format($bal, 2) }}</td>
        </tr>
    @endif
</table>

<div class="pdf-gap-24">&nbsp;</div>

{{-- REMARKS --}}
<div class="pdf-section-label">Remarks</div>
<div class="pdf-gap-8">&nbsp;</div>
<table style="border-collapse:collapse; width:100%;">
    <tr>
        <td class="pdf-remark" style="vertical-align:top; padding:0 8px 5px 0; width:16px;">1.</td>
        <td class="pdf-remark" style="padding:0 0 5px 0;">Materials for ads must be delivered 7 days before the campaign starts.</td>
    </tr>
    <tr>
        <td class="pdf-remark" style="vertical-align:top; padding:0 8px 5px 0;">2.</td>
        <td class="pdf-remark" style="padding:0 0 5px 0;">Orders may not be cancelled by advertiser and all advertisements are subject.</td>
    </tr>
    <tr>
        <td class="pdf-remark" style="vertical-align:top; padding:0 8px 5px 0;">3.</td>
        <td class="pdf-remark" style="padding:0 0 5px 0;">All Cheque payments to be made via crossed cheque or Telegraphic Transfer to ROTIKAYA MEDIA SDN BHD (bank account details available upon request).</td>
    </tr>
</table>
<div class="pdf-gap-8">&nbsp;</div>
<div class="pdf-small">This invoice is computer generated. No signature is required.</div>

@include('pdf._footer', ['footerNote' => config('company.name') . ' · This is a computer-generated invoice.'])

</body>
</html>
