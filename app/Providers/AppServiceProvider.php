<?php

namespace App\Providers;

use App\Services\BackupDumpRunner;
use App\Services\MysqldumpRunner;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BackupDumpRunner::class, MysqldumpRunner::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
