{{-- Shared PDF footer, fixed to the bottom of every page.
     Params:
       $footerNote string  left-aligned note (optional) --}}
@php
    $footerNote = $footerNote ?? null;
@endphp
<div class="pdf-footer">
    <div class="pdf-footer-line">&nbsp;</div>
    <div class="pdf-gap-8">&nbsp;</div>
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:72%; vertical-align:middle;">
                @if($footerNote)
                    <span class="pdf-footer-text">{{ $footerNote }}</span>
                @endif
            </td>
            <td class="pdf-footer-text" style="width:28%; text-align:right; vertical-align:middle;">
                Page <script type="text/php">echo $PAGE_NUM . ' / ' . $PAGE_COUNT;</script>
            </td>
        </tr>
    </table>
</div>
