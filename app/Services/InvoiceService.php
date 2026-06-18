<?php

namespace App\Services;

use App\Exceptions\TemplateNotFoundException;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\Sale;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceService
{
    public function generateFromSale(Sale $sale): Invoice
    {
        $template = InvoiceTemplate::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();

        if (! $template) {
            throw new TemplateNotFoundException('No invoice template configured. Please contact administrator.');
        }

        $settings = SystemSetting::query()->first();
        $termDays = $settings?->default_payment_term_days ?? 30;

        return Invoice::query()->create([
            'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'sale_id' => $sale->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays($termDays)->toDateString(),
            'total_amount' => $sale->amount,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);
    }

    public function storeInvoicePdf(Invoice $invoice): string
    {
        $invoice->loadMissing(['sale.client']);

        $pdf = app('dompdf.wrapper')->loadView('invoices.pdf', [
            'invoice' => $invoice,
            'generatedAt' => now(),
        ]);

        $path = 'invoices/' . $invoice->invoice_number . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}
