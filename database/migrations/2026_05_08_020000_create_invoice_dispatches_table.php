<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('channel');
            $table->foreignId('dispatched_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['sent', 'failed']);
            $table->string('recipient');
            $table->text('message_body');
            $table->text('error_message')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('dispatched_at');
            $table->timestamps();

            $table->index(['invoice_id', 'status', 'dispatched_at']);
            $table->index('channel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_dispatches');
    }
};
