<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Generates ~24 months of realistic historical sales, invoices and payments
 * for testing/UAT, so dashboards, reports and charts are well populated.
 *
 * Standalone and additive — it reuses existing users/clients and ADDS data;
 * it never truncates. Safe to run more than once (invoice numbers are seeded
 * from the current row count to stay unique across runs).
 *
 * Run it:
 *   php artisan db:seed --class=DemoHistorySeeder --force
 *
 * Uses no faker (works on a production --no-dev build) and suppresses model
 * events via WithoutModelEvents so it doesn't flood the audit log.
 */
class DemoHistorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $salespeople = $this->resolveSalespeople();
        if ($salespeople->isEmpty()) {
            $this->command?->error('No users found to assign as salespeople. Run the base DatabaseSeeder first.');

            return;
        }

        $clients = $this->resolveClients();

        $campaignPrefixes = [
            'Year-End', 'Ramadan', 'Hari Raya', 'Merdeka', 'Back to School',
            'Mid-Year', 'New Year', 'Q1', 'Q2', 'Q3', 'Q4', 'Mega', 'Flash',
            'Festive', 'Deepavali', 'CNY', 'School Holiday', 'Weekend',
        ];
        $campaignTypes = [
            'Movie Promo', 'Brand Awareness Campaign', 'Digital Marketing Package',
            'Banner Ads', 'Radio Spot Buy', 'Billboard Campaign', 'Social Media Blitz',
            'Print Feature', 'Cinema Pre-Roll', 'Influencer Collaboration',
            'Outdoor Advertising', 'Sponsored Content',
        ];
        $methods = ['bank_transfer', 'cheque', 'online', 'cash'];

        // Unique, monotonic invoice sequence that survives re-runs.
        $seq = (int) (Invoice::max('id') ?? 0) + 1000;

        $createdSales = 0;
        $createdInvoices = 0;
        $createdPayments = 0;

        // Walk month by month from 24 months ago up to the current month.
        for ($monthsAgo = 24; $monthsAgo >= 0; $monthsAgo--) {
            $monthStart = Carbon::now()->startOfMonth()->subMonths($monthsAgo);
            $salesThisMonth = random_int(4, 9);

            for ($i = 0; $i < $salesThisMonth; $i++) {
                $saleDate = $monthStart->copy()->addDays(random_int(0, 26))
                    ->addHours(random_int(8, 18));

                $client = $clients->random();
                $salesperson = $salespeople->random();
                $amount = (float) (random_int(30, 350) * 100); // RM 3,000 – 35,000
                $campaign = $campaignPrefixes[array_rand($campaignPrefixes)]
                    . ' ' . $campaignTypes[array_rand($campaignTypes)];

                // Recent months keep some sales still "pending" (no invoice yet).
                $isCompleted = $monthsAgo > 1 ? (random_int(1, 100) <= 90) : (random_int(1, 100) <= 55);

                $sale = Sale::query()->create([
                    'client_id' => $client->id,
                    'salesperson_id' => $salesperson->id,
                    'campaign_name' => $campaign,
                    'amount' => $amount,
                    'status' => $isCompleted ? 'completed' : 'pending',
                    'start_date' => $saleDate->copy()->addDays(random_int(3, 20)),
                    'end_date' => $saleDate->copy()->addDays(random_int(30, 60)),
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                ]);
                $createdSales++;

                if (! $isCompleted) {
                    continue;
                }

                // Invoice raised a few days after the sale completes.
                $issueDate = $saleDate->copy()->addDays(random_int(1, 7));
                $dueDate = $issueDate->copy()->addDays(30);
                $isPastDue = $dueDate->isPast();

                [$status, $paidAmount, $payments] = $this->resolveInvoiceOutcome(
                    $amount,
                    $issueDate,
                    $dueDate,
                    $isPastDue
                );

                $seq++;
                $invoice = Invoice::query()->create([
                    'invoice_number' => 'INV-' . $issueDate->format('Y') . '-' . str_pad((string) $seq, 5, '0', STR_PAD_LEFT),
                    'sale_id' => $sale->id,
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate,
                    'total_amount' => $amount,
                    'paid_amount' => $paidAmount,
                    'status' => $status,
                    'created_at' => $issueDate,
                    'updated_at' => $issueDate,
                ]);
                $createdInvoices++;

                foreach ($payments as $payment) {
                    Payment::query()->create([
                        'invoice_id' => $invoice->id,
                        'amount' => $payment['amount'],
                        'payment_date' => $payment['date'],
                        'method' => $methods[array_rand($methods)],
                        'reference' => 'REF-' . strtoupper(substr(md5((string) random_int(1, PHP_INT_MAX)), 0, 8)),
                        'notes' => $payment['note'],
                        'created_at' => $payment['date'],
                        'updated_at' => $payment['date'],
                    ]);
                    $createdPayments++;
                }
            }
        }

        $this->command?->info("DemoHistorySeeder done: +{$createdSales} sales, +{$createdInvoices} invoices, +{$createdPayments} payments across 24 months.");
    }

    /**
     * Decide an invoice's status, paid amount and resulting payment rows.
     *
     * @return array{0:string,1:float,2:array<int,array{amount:float,date:Carbon,note:string}>}
     */
    private function resolveInvoiceOutcome(float $amount, Carbon $issueDate, Carbon $dueDate, bool $isPastDue): array
    {
        $roll = random_int(1, 100);

        if ($isPastDue) {
            // Most historical invoices are settled; a minority lingers.
            if ($roll <= 78) {
                $payDate = $this->dateBetween($issueDate, $dueDate->copy()->addDays(10));

                return ['paid', $amount, [[
                    'amount' => $amount,
                    'date' => $payDate,
                    'note' => 'Full payment received',
                ]]];
            }

            if ($roll <= 90) {
                $partial = round($amount * (random_int(30, 70) / 100), 2);
                $payDate = $this->dateBetween($issueDate, $dueDate);

                return ['partial', $partial, [[
                    'amount' => $partial,
                    'date' => $payDate,
                    'note' => 'Partial payment',
                ]]];
            }

            return ['overdue', 0.0, []];
        }

        // Not yet past due (recent invoices).
        if ($roll <= 45) {
            $payDate = $this->dateBetween($issueDate, Carbon::now());

            return ['paid', $amount, [[
                'amount' => $amount,
                'date' => $payDate,
                'note' => 'Full payment received',
            ]]];
        }

        if ($roll <= 70) {
            $partial = round($amount * (random_int(20, 60) / 100), 2);
            $payDate = $this->dateBetween($issueDate, Carbon::now());

            return ['partial', $partial, [[
                'amount' => $partial,
                'date' => $payDate,
                'note' => 'Partial payment',
            ]]];
        }

        return ['unpaid', 0.0, []];
    }

    private function dateBetween(Carbon $start, Carbon $end): Carbon
    {
        $start = $start->copy();
        $end = $end->copy();
        if ($end->lessThanOrEqualTo($start)) {
            return $start;
        }
        $span = $start->diffInDays($end);

        return $start->addDays(random_int(0, (int) $span));
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function resolveSalespeople()
    {
        $salesRole = Role::query()->where('name', 'sales_staff')->first();
        if ($salesRole) {
            $users = User::query()->where('role_id', $salesRole->id)->get();
            if ($users->isNotEmpty()) {
                return $users;
            }
        }

        return User::query()->get();
    }

    /**
     * Reuse existing clients and top up to a varied pool for the demo.
     *
     * @return \Illuminate\Support\Collection<int, Client>
     */
    private function resolveClients()
    {
        $extra = [
            ['name' => 'MAJU JAYA', 'company_name' => 'Maju Jaya Enterprise Sdn Bhd', 'email' => 'ads@majujaya.com.my', 'phone' => '+60 3-6201 8800', 'address' => 'Kuala Lumpur, Malaysia'],
            ['name' => 'SEGAR FRESH', 'company_name' => 'Segar Fresh Mart Sdn Bhd', 'email' => 'marketing@segar.com.my', 'phone' => '+60 3-5519 2211', 'address' => 'Subang Jaya, Selangor'],
            ['name' => 'TECHNOVATE', 'company_name' => 'Technovate Systems Sdn Bhd', 'email' => 'promo@technovate.my', 'phone' => '+60 3-2780 6600', 'address' => 'Cyberjaya, Selangor'],
            ['name' => 'KOPI TIAM CO', 'company_name' => 'Kopitiam Heritage Sdn Bhd', 'email' => 'hello@kopitiam.my', 'phone' => '+60 4-261 7788', 'address' => 'Georgetown, Penang'],
            ['name' => 'AUTOZONE', 'company_name' => 'Autozone Motors Sdn Bhd', 'email' => 'ads@autozone.com.my', 'phone' => '+60 7-554 1199', 'address' => 'Johor Bahru, Johor'],
            ['name' => 'SIHAT WELLNESS', 'company_name' => 'Sihat Wellness Group', 'email' => 'marketing@sihat.my', 'phone' => '+60 3-7845 3322', 'address' => 'Petaling Jaya, Selangor'],
            ['name' => 'BORNEO TRAVELS', 'company_name' => 'Borneo Travels & Tours', 'email' => 'promo@borneotravels.my', 'phone' => '+60 88-233 445', 'address' => 'Kota Kinabalu, Sabah'],
            ['name' => 'URBAN THREADS', 'company_name' => 'Urban Threads Apparel Sdn Bhd', 'email' => 'brand@urbanthreads.my', 'phone' => '+60 3-9201 4455', 'address' => 'Cheras, Kuala Lumpur'],
            ['name' => 'GREENLEAF', 'company_name' => 'Greenleaf Organics Sdn Bhd', 'email' => 'ads@greenleaf.com.my', 'phone' => '+60 3-8023 6677', 'address' => 'Putrajaya, Malaysia'],
            ['name' => 'PRIMA BUILD', 'company_name' => 'Prima Build Materials Sdn Bhd', 'email' => 'marketing@primabuild.my', 'phone' => '+60 6-281 9900', 'address' => 'Melaka, Malaysia'],
        ];

        foreach ($extra as $data) {
            Client::query()->firstOrCreate(['name' => $data['name']], $data);
        }

        return Client::query()->get();
    }
}
