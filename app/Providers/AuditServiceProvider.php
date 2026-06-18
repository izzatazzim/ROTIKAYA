<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\InvoiceDispatch;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use App\Observers\AuditLogObserver;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register the audit observer for tracked models
        Invoice::observe(AuditLogObserver::class);
        InvoiceDispatch::observe(AuditLogObserver::class);
        Sale::observe(AuditLogObserver::class);
        Payment::observe(AuditLogObserver::class);
        User::observe(AuditLogObserver::class);
    }
}
