<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\InvoicesController;
use App\Http\Controllers\Web\PaymentsController;
use App\Http\Controllers\Web\PermissionsController;
use App\Http\Controllers\Web\RemindersController;
use App\Http\Controllers\Web\ReportsController;
use App\Http\Controllers\Web\SalesController;
use App\Http\Controllers\Web\SystemSettingsController;
use App\Http\Controllers\Web\UsersController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/sales/new', [SalesController::class, 'index'])->middleware('role:admin,sales_staff')->name('sales.index');
    Route::post('/sales', [SalesController::class, 'store'])->middleware('role:admin,sales_staff')->name('sales.store');
    Route::get('/clients', [SalesController::class, 'clients'])->middleware('role:admin,sales_staff')->name('clients.index');
    Route::post('/clients', [SalesController::class, 'storeClient'])->middleware('role:admin,sales_staff')->name('clients.store');

    Route::get('/invoices', [InvoicesController::class, 'index'])->middleware('role:admin,accountant,sales_staff')->name('invoices.index');
    Route::post('/invoices', [InvoicesController::class, 'store'])->middleware('role:admin,accountant')->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoicesController::class, 'show'])->middleware('role:admin,accountant,sales_staff')->name('invoices.show');
    Route::post('/invoices/{invoice}/send', [InvoicesController::class, 'send'])->middleware('role:admin,accountant')->name('invoices.send');

    Route::get('/payments', [PaymentsController::class, 'index'])->middleware('role:admin,accountant')->name('payments.index');
    Route::post('/payments', [PaymentsController::class, 'store'])->middleware('role:admin,accountant')->name('payments.store');

    Route::post('/reminders/trigger', [RemindersController::class, 'trigger'])->middleware('role:admin,accountant')->name('reminders.trigger');

    Route::get('/reports', [ReportsController::class, 'index'])->middleware('role:admin,accountant')->name('reports.index');
    Route::get('/reports/download/{type}', [ReportsController::class, 'downloadPdf'])->middleware('role:admin,accountant')->name('reports.download');
    Route::post('/reports/revenue/export-pdf', [ReportsController::class, 'exportRevenuePdf'])->middleware('role:admin,accountant')->name('reports.revenue.export-pdf');
    Route::post('/reports/revenue/export-xlsx', [ReportsController::class, 'exportRevenueXlsx'])->middleware('role:admin,accountant')->name('reports.revenue.export-xlsx');
    Route::get('/reports/client-statement', [ReportsController::class, 'clientStatementForm'])->middleware('role:admin,accountant')->name('reports.client-statement');
    Route::post('/reports/client-statement/export', [ReportsController::class, 'exportClientStatement'])->middleware('role:admin,accountant')->name('reports.client-statement.export');

    Route::get('/users', [UsersController::class, 'index'])->middleware('role:admin')->name('users.index');
    Route::post('/users', [UsersController::class, 'store'])->middleware('role:admin')->name('users.store');
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->middleware('role:admin')->name('users.edit');
    Route::put('/users/{user}', [UsersController::class, 'update'])->middleware('role:admin')->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->middleware('role:admin')->name('users.destroy');

    Route::get('/settings', [SystemSettingsController::class, 'index'])->middleware('role:admin')->name('settings.index');
    Route::post('/settings', [SystemSettingsController::class, 'update'])->middleware('role:admin')->name('settings.update');
    Route::post('/settings/backup/run', [SystemSettingsController::class, 'runBackup'])->middleware('role:admin')->name('settings.backup.run');
    Route::get('/settings/backup/{backup}/download', [SystemSettingsController::class, 'downloadBackup'])->middleware('role:admin')->name('settings.backup.download');
    Route::get('/permissions', [PermissionsController::class, 'index'])->middleware('role:admin')->name('permissions.index');
});
