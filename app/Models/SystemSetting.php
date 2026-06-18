<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['default_payment_term_days', 'reminder_intervals', 'invoice_template'];

    protected function casts(): array
    {
        return ['reminder_intervals' => 'array'];
    }
}
