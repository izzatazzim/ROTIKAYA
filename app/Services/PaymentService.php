<?php

namespace App\Services;

use App\Models\Invoice;

class PaymentService
{
    public function refreshInvoiceStatus(Invoice $invoice): Invoice
    {
        $paidAmount = (float) $invoice->payments()->sum('amount');
        $status = 'unpaid';

        if ($paidAmount >= (float) $invoice->total_amount) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        } elseif ($invoice->due_date->isPast()) {
            $status = 'overdue';
        }

        $invoice->update([
            'paid_amount' => $paidAmount,
            'status' => $status,
        ]);

        return $invoice->refresh();
    }
}
