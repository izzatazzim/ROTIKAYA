{{-- Shared PDF header.
     Params:
       $docTitle  string  e.g. 'INVOICE', 'STATEMENT', 'REVENUE REPORT'
       $metaRows  array   ordered ['Label' => 'Value', ...] shown top-right --}}
@php
    $logoPath = public_path('images/rotikaya-logo-downloads.png');
    $logoBase64 = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;
    $metaRows = $metaRows ?? [];
@endphp
<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="vertical-align:top; width:55%;">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Rotikaya Media" style="height:42px; width:auto;">
            @else
                <span class="pdf-company-name">ROTIKAYA</span>
            @endif
            <div class="pdf-gap-8">&nbsp;</div>
            <div class="pdf-company-name">ROTIKAYA MEDIA SDN BHD</div>
            <div class="pdf-reg-line">Reg No: 201301044709 / 1074532-H</div>
            <div class="pdf-reg-line">Service Tax ID: W10-1809-32000845</div>
            <div class="pdf-gap-6">&nbsp;</div>
            <div class="pdf-address">
                No. 22A-1, Jalan Teknologi 3/6D,<br>
                Taman Sains Selangor 1,<br>
                Seksyen 3, Kota Damansara,<br>
                47810 Petaling Jaya, Selangor<br>
                Malaysia
            </div>
            <div class="pdf-gap-4">&nbsp;</div>
            <div class="pdf-contact">Contact: +60 13-396 1146 / hi@rotikaya.com</div>
        </td>
        <td style="vertical-align:top; text-align:right; width:45%;">
            <div class="pdf-doc-title">{{ $docTitle }}</div>
            <div class="pdf-gap-12">&nbsp;</div>
            <table style="border-collapse:collapse; margin-left:auto;">
                @foreach($metaRows as $label => $value)
                    <tr>
                        <td class="pdf-meta-label" style="padding:3px 8px 3px 0; white-space:nowrap;">{{ $label }}</td>
                        <td class="pdf-meta-value" style="padding:3px 0; white-space:nowrap;">{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>

<div class="pdf-gap-12">&nbsp;</div>
<div class="pdf-accent">&nbsp;</div>
<div class="pdf-gap-24">&nbsp;</div>
