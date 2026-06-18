<?php

namespace App\Services;

use App\Models\Reminder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * WhatsApp messaging service.
 *
 * NOTE: This is a simulated provider for development and academic
 * demonstration. It does NOT send real WhatsApp messages.
 *
 * Production integration plan:
 * - Replace simulator driver with Meta WhatsApp Business API
 * - Or use Twilio WhatsApp API (twilio/sdk package)
 * - Required env vars are documented in .env.example
 * - The send() interface (signature + return contract) MUST remain
 *   stable so consumers (ReminderService, InvoiceDispatchService)
 *   work without modification
 *
 * See docs/PROJECT_SPEC.md -> "Future Enhancements: WhatsApp Production
 * Integration" for the full migration guide.
 */
class WhatsAppService
{
    public function send(string $recipient, string $message, ?string $attachmentPath = null): array
    {
        $driver = (string) config('services.whatsapp.driver', 'simulator');

        if ($driver === 'simulator') {
            return $this->sendWithSimulator($recipient, $message, $attachmentPath);
        }

        return [
            'success' => false,
            'message_id' => null,
            'error' => "Unsupported WhatsApp driver [{$driver}] for this environment.",
            'driver' => $driver,
            'simulated' => false,
        ];
    }

    public function sendReminder(Reminder $reminder, string $phone): array
    {
        return $this->send($phone, $reminder->message);
    }

    private function sendWithSimulator(string $recipient, string $message, ?string $attachmentPath = null): array
    {
        $mode = (string) config('services.whatsapp.simulate_mode', 'always_success');
        $failureRate = (int) config('services.whatsapp.simulate_failure_rate', 30);
        $success = match ($mode) {
            'always_fail' => false,
            'random' => $this->randomPercent() > max(0, min(100, $failureRate)),
            default => true,
        };

        $response = [
            'success' => $success,
            'message_id' => $success ? (string) Str::uuid() : null,
            'error' => $success ? null : "Simulated WhatsApp failure in mode [{$mode}].",
            'driver' => 'simulator',
            'simulated' => true,
        ];

        Log::info('WhatsApp simulator send attempt', [
            'recipient' => $recipient,
            'mode' => $mode,
            'failure_rate' => $failureRate,
            'attachment_path' => $attachmentPath,
            'result' => $response,
        ]);

        return $response;
    }

    protected function randomPercent(): int
    {
        return mt_rand(1, 100);
    }
}
