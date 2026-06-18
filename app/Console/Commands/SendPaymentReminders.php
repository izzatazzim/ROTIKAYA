<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Send payment reminders for overdue invoices';

    public function __construct(private readonly ReminderService $reminderService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $processed = $this->reminderService->processOverdueInvoices();
        $this->info("Processed {$processed} payment reminders.");

        return self::SUCCESS;
    }
}
