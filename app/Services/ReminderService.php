<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentReminder;
use App\Models\Reminder;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Mail;

class ReminderService
{
    public function __construct(private readonly WhatsAppService $whatsAppService)
    {
    }

    public function processOverdueInvoices(): int
    {
        $settings = SystemSetting::query()->first();
        $intervals = $settings?->reminder_intervals ?? [15, 30, 45];
        $processed = 0;

        $invoices = Invoice::query()
            ->with(['sale.client'])
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->get();

        foreach ($invoices as $invoice) {
            $daysOverdue = max(
                0,
                $invoice->due_date->copy()->startOfDay()->diffInDays(now()->startOfDay(), false)
            );
            foreach ($intervals as $interval) {
                if ($daysOverdue < (int) $interval) {
                    continue;
                }

                $exists = Reminder::query()
                    ->where('invoice_id', $invoice->id)
                    ->where('days_overdue', (int) $interval)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $reminder = Reminder::query()->create([
                    'invoice_id' => $invoice->id,
                    'days_overdue' => (int) $interval,
                    'channel' => 'whatsapp',
                    'status' => 'pending',
                    'message' => $this->buildReminderMessage($invoice, (int) $daysOverdue),
                ]);

                $phone = $invoice->sale->client->phone ?? '';
                $message = $reminder->message;
                $sent = false;
                $whatsAppError = null;

                try {
                    if ($phone !== '') {
                        $whatsAppResult = $this->whatsAppService->sendReminder($reminder, $phone);
                        $sent = (bool) ($whatsAppResult['success'] ?? false);
                        $whatsAppError = $whatsAppResult['error'] ?? null;
                    }
                } catch (\Throwable $exception) {
                    $whatsAppError = $exception->getMessage();
                    $sent = false;
                }

                PaymentReminder::query()->create([
                    'invoice_id' => $invoice->id,
                    'days_overdue' => (int) $interval,
                    'channel' => 'whatsapp',
                    'status' => $sent ? 'sent' : 'failed',
                    'recipient' => $phone !== '' ? $phone : null,
                    'message' => $message,
                    'error_message' => $whatsAppError,
                    'sent_at' => $sent ? now() : null,
                ]);

                // Fallback channel: email is attempted only when WhatsApp fails.
                if (! $sent) {
                    $email = $invoice->sale->client->email ?? '';
                    $emailSent = false;
                    $emailError = null;

                    try {
                        if ($email !== '') {
                            Mail::raw($message, function ($mail) use ($email): void {
                                $mail->to($email)->subject('Overdue Invoice Reminder');
                            });
                            $emailSent = true;
                        }
                    } catch (\Throwable $exception) {
                        $emailError = $exception->getMessage();
                        $emailSent = false;
                    }

                    PaymentReminder::query()->create([
                        'invoice_id' => $invoice->id,
                        'days_overdue' => (int) $interval,
                        'channel' => 'email',
                        'status' => $emailSent ? 'sent' : 'failed',
                        'recipient' => $email !== '' ? $email : null,
                        'message' => $message,
                        'error_message' => $emailError,
                        'sent_at' => $emailSent ? now() : null,
                    ]);
                }

                $reminder->update([
                    'status' => $sent ? 'sent' : 'failed',
                    'sent_at' => $sent ? now() : null,
                ]);
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Build the customer-facing overdue reminder message.
     *
     * Sent to the CUSTOMER (sale.client), never to internal staff. Includes
     * customer name, invoice number, outstanding amount, original due date,
     * days overdue, payment instructions (bank details), and the Rotikaya
     * Media reference. The same body is reused for the WhatsApp send and the
     * email fallback so both channels carry identical information.
     */
    private function buildReminderMessage(Invoice $invoice, int $daysOverdue): string
    {
        $client = $invoice->sale?->client;
        $name = $client?->name ?? 'Customer';
        $outstanding = max(0.0, (float) $invoice->total_amount - (float) $invoice->paid_amount);
        $amount = number_format($outstanding, 2);
        $dueDate = optional($invoice->due_date)->format('d M Y');
        $bankDetails = (string) config('company.bank_details');

        return implode("\n", [
            "Hello {$name},",
            '',
            'This is a payment reminder from Rotikaya Media.',
            '',
            "Invoice: {$invoice->invoice_number}",
            "Amount due: RM{$amount}",
            "Original due date: {$dueDate}",
            "Status: {$daysOverdue} day(s) overdue",
            '',
            'Payment instructions:',
            $bankDetails,
            '',
            'Kindly arrange payment at your earliest convenience. Thank you.',
            '— Rotikaya Media',
        ]);
    }
}
