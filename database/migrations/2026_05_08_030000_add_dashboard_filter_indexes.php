<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('issue_date');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index('client_id');
            $table->index('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['issue_date']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['client_id']);
            $table->dropIndex(['start_date']);
        });
    }
};
