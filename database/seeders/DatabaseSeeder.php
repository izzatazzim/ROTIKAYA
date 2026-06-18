<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create Roles
        $adminRole = Role::query()->create(['name' => 'admin', 'description' => 'System administrator with full access']);
        $accountantRole = Role::query()->create(['name' => 'accountant', 'description' => 'Finance, invoices, and reporting']);
        $salesRole = Role::query()->create(['name' => 'sales_staff', 'description' => 'Sales operations and client management']);

        // Create Users
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@rotikaya.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'last_login_at' => now()->subHours(2),
        ]);

        $accountant = User::query()->create([
            'name' => 'Ahmad Accountant',
            'email' => 'accountant@rotikaya.com',
            'password' => Hash::make('password'),
            'role_id' => $accountantRole->id,
            'last_login_at' => now()->subHours(5),
        ]);

        $sales = User::query()->create([
            'name' => 'Sarah Sales',
            'email' => 'sales@rotikaya.com',
            'password' => Hash::make('password'),
            'role_id' => $salesRole->id,
            'last_login_at' => now()->subDay(),
        ]);

        // Create additional sales staff
        $sales2 = User::query()->create([
            'name' => 'Ali Ahmad',
            'email' => 'ali@rotikaya.com',
            'password' => Hash::make('password'),
            'role_id' => $salesRole->id,
            'last_login_at' => now()->subDays(3),
        ]);

        InvoiceTemplate::query()->create([
            'name' => 'Default Invoice Template',
            'is_active' => true,
            'is_default' => true,
            'content' => json_encode([
                'header' => 'Rotikaya Media Invoice',
                'body' => 'Invoice for {client_name} campaign {campaign_name}.',
                'footer' => 'Thank you for your business.',
            ], JSON_THROW_ON_ERROR),
            'created_by' => $admin->id,
        ]);

        // Create 5 Sample Clients
        $clients = [
            ['name' => 'GSC MOVIES', 'company_name' => 'Golden Screen Cinemas Sdn Bhd', 'email' => 'marketing@gsc.com.my', 'phone' => '+60 3-7493 3222', 'address' => 'Kuala Lumpur, Malaysia'],
            ['name' => 'Hitman Solution', 'company_name' => 'Hitman Solution Sdn Bhd', 'email' => 'contact@hitman.my', 'phone' => '+60 3-8888 1234', 'address' => 'Petaling Jaya, Selangor'],
            ['name' => 'POPULAR BOOK CO', 'company_name' => 'Popular Book Co (M) Sdn Bhd', 'email' => 'ads@popular.com.my', 'phone' => '+60 3-7960 8833', 'address' => 'Shah Alam, Selangor'],
            ['name' => 'NIDAH WATER', 'company_name' => 'Nidah Water Industries', 'email' => 'marketing@nidah.com.my', 'phone' => '+60 4-222 3344', 'address' => 'Georgetown, Penang'],
            ['name' => 'SWIFT', 'company_name' => 'Swift Logistics Malaysia', 'email' => 'promo@swift.my', 'phone' => '+60 7-333 4455', 'address' => 'Johor Bahru, Johor'],
        ];

        $createdClients = [];
        foreach ($clients as $clientData) {
            $createdClients[] = Client::query()->create($clientData);
        }

        // Create 10 Sample Sales with mixed statuses
        $salesData = [
            ['client_idx' => 0, 'salesperson' => $sales, 'campaign' => 'Year-End Movie Promo', 'amount' => 25000.00, 'status' => 'completed', 'days_ago' => 60],
            ['client_idx' => 0, 'salesperson' => $sales, 'campaign' => 'Blockbuster Summer Campaign', 'amount' => 18500.00, 'status' => 'completed', 'days_ago' => 45],
            ['client_idx' => 1, 'salesperson' => $sales2, 'campaign' => 'Digital Marketing Package', 'amount' => 12000.00, 'status' => 'completed', 'days_ago' => 40],
            ['client_idx' => 2, 'salesperson' => $sales, 'campaign' => 'Back to School Ads', 'amount' => 8500.00, 'status' => 'completed', 'days_ago' => 35],
            ['client_idx' => 3, 'salesperson' => $sales2, 'campaign' => 'Healthy Living Campaign', 'amount' => 15000.00, 'status' => 'completed', 'days_ago' => 30],
            ['client_idx' => 4, 'salesperson' => $sales, 'campaign' => 'Express Delivery Promo', 'amount' => 22000.00, 'status' => 'completed', 'days_ago' => 25],
            ['client_idx' => 1, 'salesperson' => $sales, 'campaign' => 'Tech Solutions Banner Ads', 'amount' => 9500.00, 'status' => 'completed', 'days_ago' => 20],
            ['client_idx' => 2, 'salesperson' => $sales2, 'campaign' => 'Holiday Reading Festival', 'amount' => 11000.00, 'status' => 'pending', 'days_ago' => 10],
            ['client_idx' => 3, 'salesperson' => $sales, 'campaign' => 'Q4 Brand Awareness', 'amount' => 16000.00, 'status' => 'pending', 'days_ago' => 5],
            ['client_idx' => 4, 'salesperson' => $sales2, 'campaign' => 'New Year Rush Campaign', 'amount' => 28000.00, 'status' => 'pending', 'days_ago' => 2],
        ];

        $createdSales = [];
        foreach ($salesData as $saleData) {
            $createdSales[] = Sale::query()->create([
                'client_id' => $createdClients[$saleData['client_idx']]->id,
                'salesperson_id' => $saleData['salesperson']->id,
                'campaign_name' => $saleData['campaign'],
                'amount' => $saleData['amount'],
                'status' => $saleData['status'],
                'start_date' => now()->subDays($saleData['days_ago']),
                'end_date' => now()->subDays($saleData['days_ago'])->addDays(30),
                'created_at' => now()->subDays($saleData['days_ago']),
            ]);
        }

        // Create Invoices for completed sales with various statuses
        $invoiceStatuses = [
            ['sale_idx' => 0, 'status' => 'paid', 'paid_amount' => 25000.00, 'days_ago' => 55],
            ['sale_idx' => 1, 'status' => 'overdue', 'paid_amount' => 0, 'days_ago' => 40],
            ['sale_idx' => 2, 'status' => 'partial', 'paid_amount' => 5000.00, 'days_ago' => 35],
            ['sale_idx' => 3, 'status' => 'paid', 'paid_amount' => 8500.00, 'days_ago' => 30],
            ['sale_idx' => 4, 'status' => 'overdue', 'paid_amount' => 0, 'days_ago' => 25],
            ['sale_idx' => 5, 'status' => 'unpaid', 'paid_amount' => 0, 'days_ago' => 20],
            ['sale_idx' => 6, 'status' => 'partial', 'paid_amount' => 4000.00, 'days_ago' => 15],
        ];

        $invoiceNumber = 1;
        foreach ($invoiceStatuses as $invData) {
            $sale = $createdSales[$invData['sale_idx']];
            $issueDate = now()->subDays($invData['days_ago']);
            $dueDate = $issueDate->copy()->addDays(30);

            $invoice = Invoice::query()->create([
                'invoice_number' => 'INV-' . date('Y') . '-' . str_pad($invoiceNumber, 4, '0', STR_PAD_LEFT),
                'sale_id' => $sale->id,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'total_amount' => $sale->amount,
                'paid_amount' => $invData['paid_amount'],
                'status' => $invData['status'],
            ]);

            // Create payments for paid/partial invoices
            if ($invData['paid_amount'] > 0) {
                Payment::query()->create([
                    'invoice_id' => $invoice->id,
                    'amount' => $invData['paid_amount'],
                    'payment_date' => $issueDate->copy()->addDays(rand(5, 15)),
                    'method' => ['bank_transfer', 'cheque', 'online'][rand(0, 2)],
                    'reference' => 'REF-' . strtoupper(substr(md5(rand()), 0, 8)),
                    'notes' => $invData['paid_amount'] == $sale->amount ? 'Full payment received' : 'Partial payment',
                ]);
            }

            $invoiceNumber++;
        }

        // Create System Settings
        SystemSetting::query()->create([
            'default_payment_term_days' => 30,
            'reminder_intervals' => [15, 30, 45],
            'invoice_template' => 'Dear {client_name}, this is a reminder for invoice {invoice_number}. Please settle the outstanding amount of RM{amount} by {due_date}. Thank you for your business.',
        ]);
    }
}
