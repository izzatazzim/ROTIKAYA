<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceDispatch;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InvoiceDispatchService
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly WhatsAppService $whatsAppService,
    ) {
    }

    public function dispatch(Invoice $invoice, User $dispatcher): array
    {
        $invoice->loadMissing(['sale.client']);
        $client = $invoice->sale?->client;
        $pdfPath = $this->invoiceService->storeInvoicePdf($invoice);
        $message = $this->buildMessage($invoice);
        $errors = [];

        $phone = (string) ($client?->phone ?? '');
        if ($phone !== '') {
            try {
                $result = $this->whatsAppService->send($phone, $message, $pdfPath);
                $sent = (bool) ($result['success'] ?? false);
                if ($sent) {
                    $this->createDispatchRow($invoice, $dispatcher, 'whatsapp', 'sent', $phone, $message, null, $pdfPath);
                    return [
                        'success' => true,
                        'status' => 'sent',
                        'channel' => 'whatsapp',
                    ];
                }

                $error = (string) ($result['error'] ?? 'WhatsApp service returned failed status.');
                $errors[] = $error;
                $this->createDispatchRow($invoice, $dispatcher, 'whatsapp', 'failed', $phone, $message, $error, $pdfPath);
            } catch (Throwable $throwable) {
                $error = 'WhatsApp send failed: ' . $throwable->getMessage();
                $errors[] = $error;
                $this->createDispatchRow($invoice, $dispatcher, 'whatsapp', 'failed', $phone, $message, $error, $pdfPath);
            }
        } else {
            $errors[] = 'Client phone is missing.';
        }

        $email = (string) ($client?->email ?? '');
        if ($email !== '') {
            try {
                Mail::raw($message, function ($mail) use ($email, $invoice, $pdfPath) {
                    $mail->to($email)
                        ->subject('Invoice ' . $invoice->invoice_number)
                        ->attach(storage_path('app/public/' . $pdfPath));
                });
                $this->createDispatchRow($invoice, $dispatcher, 'email', 'sent', $email, $message, null, $pdfPath);

                return [
                    'success' => true,
                    'status' => 'sent',
                    'channel' => 'email',
                ];
            } catch (Throwable $throwable) {
                $error = 'Email send failed: ' . $throwable->getMessage();
                $errors[] = $error;
                $this->createDispatchRow($invoice, $dispatcher, 'email', 'failed', $email, $message, $error, $pdfPath);
            }
        } else {
            $errors[] = 'Client email is missing.';
        }

        return [
            'success' => false,
            'status' => 'failed',
            'error' => implode(' ', $errors),
        ];
    }

    private function buildMessage(Invoice $invoice): string
    {
        $clientName = $invoice->sale?->client?->name ?? 'Client';
        $campaign = $invoice->sale?->campaign_name ?? 'Campaign';
        $issueDate = optional($invoice->issue_date)->format('Y-m-d');
        $dueDate = optional($invoice->due_date)->format('Y-m-d');
        $amount = number_format((float) $invoice->total_amount, 2);

        return sprintf(
            'Hello %s, please find attached invoice %s for %s dated %s. Amount due: RM%s. Due date: %s. — Rotikaya Media',
            $clientName,
            $invoice->invoice_number,
            $campaign,
            $issueDate,
            $amount,
            $dueDate
        );
    }

    private function createDispatchRow(
        Invoice $invoice,
        User $dispatcher,
        string $channel,
        string $status,
        string $recipient,
        string $messageBody,
        ?string $error,
        ?string $pdfPath
    ): void {
        InvoiceDispatch::query()->create([
            'invoice_id' => $invoice->id,
            'channel' => $channel,
            'dispatched_by' => $dispatcher->id,
            'status' => $status,
            'recipient' => $recipient,
            'message_body' => $messageBody,
            'error_message' => $error,
            'pdf_path' => $pdfPath,
            'dispatched_at' => now(),
        ]);
    }
}
