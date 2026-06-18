<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateInvoiceStatuses extends Command
{
    protected $signature = 'invoices:update-statuses';

    protected $description = 'Update invoice statuses based on payment and due dates';

    public function handle(): int
    {
        $today = Carbon::today();
        $updated = 0;

        // Get all non-paid invoices
        $invoices = Invoice::query()
            ->where('status', '!=', 'paid')
            ->get();

        foreach ($invoices as $invoice) {
            $originalStatus = $invoice->status;
            $newStatus = $this->calculateStatus($invoice, $today);

            if ($originalStatus !== $newStatus) {
                $invoice->status = $newStatus;
                $invoice->save();
                $updated++;
                $this->line("Invoice {$invoice->invoice_number}: {$originalStatus} -> {$newStatus}");
            }
        }

        $this->info("Updated {$updated} invoice statuses.");

        return self::SUCCESS;
    }

    private function calculateStatus(Invoice $invoice, Carbon $today): string
    {
        // If fully paid
        if ($invoice->paid_amount >= $invoice->total_amount) {
            return 'paid';
        }

        // If partially paid
        if ($invoice->paid_amount > 0 && $invoice->paid_amount < $invoice->total_amount) {
            return 'partial';
        }

        // If overdue (past due date and not paid)
        if ($today->gt($invoice->due_date)) {
            return 'overdue';
        }

        // If coming due (within 7 days)
        if ($today->diffInDays($invoice->due_date, false) <= 7 && $today->diffInDays($invoice->due_date, false) >= 0) {
            return 'coming_due';
        }

        // Otherwise outstanding
        return 'unpaid';
    }
}
