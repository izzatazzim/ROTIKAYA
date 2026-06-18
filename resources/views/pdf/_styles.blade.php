{{-- Shared PDF design system. Included once per document via @include('pdf._styles').
     Two colours only: near-black #1a1a1a and gray #888. A single red accent (#c0392b)
     under the header, and one very-light gray (#f7f7f7) for table header rows. --}}
<style>
    @page { margin: 0; }

    body {
        font-family: 'Helvetica', Arial, sans-serif;
        font-size: 9.5pt;
        color: #1a1a1a;
        line-height: 1.5;
        background: #ffffff;
        margin: 0;
        padding: 40px 45px 112px 45px;
    }

    /* ---- Header ---------------------------------------------------------- */
    .pdf-company-name { font-size: 12pt; font-weight: 700; color: #1a1a1a; }
    .pdf-reg-line     { font-size: 8pt; color: #888; }
    .pdf-address      { font-size: 8.5pt; color: #555; line-height: 1.4; }
    .pdf-contact      { font-size: 8.5pt; color: #555; }
    .pdf-doc-title {
        font-size: 26pt;
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: #1a1a1a;
    }
    .pdf-meta-label { font-size: 8.5pt; color: #888; text-align: right; }
    .pdf-meta-value { font-size: 9.5pt; font-weight: 600; color: #1a1a1a; text-align: right; }
    .pdf-accent {
        border-top: 2px solid #c0392b;
        font-size: 1pt;
        line-height: 1pt;
    }

    /* ---- Section labels & body text -------------------------------------- */
    .pdf-section-label {
        font-size: 8pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #888;
    }
    .pdf-body      { font-size: 9.5pt; color: #444; }
    .pdf-name      { font-size: 11pt; font-weight: 700; color: #1a1a1a; }
    .pdf-small     { font-size: 8pt; color: #888; }
    .pdf-remark    { font-size: 8.5pt; color: #555; line-height: 1.4; }

    /* ---- Vertical rhythm helpers ----------------------------------------- */
    .pdf-gap-24 { height: 24px; font-size: 1pt; line-height: 1pt; }
    .pdf-gap-12 { height: 12px; font-size: 1pt; line-height: 1pt; }
    .pdf-gap-8  { height: 8px;  font-size: 1pt; line-height: 1pt; }
    .pdf-gap-6  { height: 6px;  font-size: 1pt; line-height: 1pt; }
    .pdf-gap-4  { height: 4px;  font-size: 1pt; line-height: 1pt; }

    /* ---- Data tables (horizontal rules only) ----------------------------- */
    table.pdf-table { width: 100%; border-collapse: collapse; }
    table.pdf-table thead th {
        background: #f7f7f7;
        font-size: 8pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #888;
        padding: 9px 10px;
        border-bottom: 1.5px solid #ddd;
        text-align: left;
    }
    table.pdf-table thead th.num { text-align: right; }
    table.pdf-table tbody td {
        padding: 11px 10px;
        border-bottom: 0.75px solid #eee;
        font-size: 9.5pt;
        color: #444;
        line-height: 1.4;
        vertical-align: top;
    }
    table.pdf-table tbody td.num { text-align: right; color: #1a1a1a; white-space: nowrap; }
    table.pdf-table tbody tr.is-last td { border-bottom: none; }
    table.pdf-table tbody td.empty { text-align: center; color: #888; padding: 16px 10px; }
    table.pdf-table tfoot td {
        padding: 11px 10px;
        border-top: 1.5px solid #1a1a1a;
        font-size: 9.5pt;
        font-weight: 700;
        color: #1a1a1a;
    }
    table.pdf-table tfoot td.num { text-align: right; white-space: nowrap; }

    /* ---- Key / value rows (summary reports) ------------------------------ */
    table.pdf-kv { width: 100%; border-collapse: collapse; }
    table.pdf-kv td { padding: 9px 0; border-bottom: 0.75px solid #eee; }
    table.pdf-kv td.label { font-size: 9.5pt; color: #444; text-align: left; }
    table.pdf-kv td.value { font-size: 9.5pt; color: #1a1a1a; text-align: right; white-space: nowrap; }
    table.pdf-kv tr.is-last td { border-bottom: none; }

    /* ---- Totals block (right-aligned, pushed right) ---------------------- */
    table.pdf-totals { width: 42%; border-collapse: collapse; margin-left: auto; }
    table.pdf-totals td.label { text-align: right; padding: 8px 12px 8px 0; font-size: 9.5pt; color: #444; }
    table.pdf-totals td.value { text-align: right; padding: 8px 0; font-size: 9.5pt; color: #1a1a1a; white-space: nowrap; }
    table.pdf-totals tr.subtotal td { border-top: 1px solid #ccc; }
    table.pdf-totals tr.grand td {
        border-top: 1.5px solid #1a1a1a;
        font-size: 13pt;
        font-weight: 700;
        color: #1a1a1a;
        padding: 10px 12px 10px 0;
    }
    table.pdf-totals tr.grand td.value { padding: 10px 0; }
    .pdf-due { color: #c0392b; }

    /* ---- Footer (anchored to bottom of every page) ----------------------- */
    .pdf-footer { position: fixed; left: 45px; right: 45px; bottom: 30px; }
    .pdf-footer-line { border-top: 0.75px solid #ddd; font-size: 1pt; line-height: 1pt; }
    .pdf-footer-text { font-size: 8pt; color: #888; line-height: 1.4; }
</style>
