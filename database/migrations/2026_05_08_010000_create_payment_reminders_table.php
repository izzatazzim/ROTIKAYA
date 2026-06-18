<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('days_overdue');
            $table->string('channel'); // whatsapp, email
            $table->string('status'); // pending, sent, failed
            $table->string('recipient')->nullable();
            $table->text('message');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'days_overdue', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reminders');
    }
};
