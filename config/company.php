<?php

/*
|--------------------------------------------------------------------------
| Company / Billing Identity
|--------------------------------------------------------------------------
|
| Used in customer-facing notifications (payment reminders, invoices).
| The bank_details string is injected into the "Payment instructions"
| block of overdue reminder messages, so customers know where to pay.
|
*/

return [
    'name' => env('COMPANY_NAME', 'Rotikaya Media Sdn Bhd'),

    'bank_details' => env(
        'COMPANY_BANK_DETAILS',
        'Bank account details available upon request. Please quote the invoice number as your payment reference.'
    ),
];
