<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ReminderService;

class RemindersController extends Controller
{
    public function __construct(private readonly ReminderService $reminderService)
    {
    }

    public function trigger()
    {
        $this->reminderService->processOverdueInvoices();
        return back()->with('success', 'Reminder job executed successfully.');
    }
}
