<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->index('name');
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('default_payment_term_days')->default(30);
            $table->json('reminder_intervals')->nullable();
            $table->text('invoice_template')->nullable();
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('salesperson_id')->constrained('users')->cascadeOnUpdate();
            $table->string('campaign_name');
            $table->decimal('amount', 12, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('pending');
            $table->string('contract_path')->nullable();
            $table->timestamps();
            $table->index(['status', 'salesperson_id']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('sale_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status')->default('unpaid');
            $table->timestamps();
            $table->index(['status', 'due_date']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('payment_date');
        });

        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('days_overdue');
            $table->string('channel')->default('whatsapp');
            $table->string('status')->default('pending');
            $table->text('message');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['invoice_id', 'days_overdue']);
        });

        Schema::create('reports_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_by')->constrained('users')->cascadeOnUpdate();
            $table->string('report_type');
            $table->json('filters')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->index('report_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports_logs');
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('clients');
    }
};
