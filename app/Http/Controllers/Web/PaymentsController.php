<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;

class PaymentsController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function index()
    {
        return view('payments.index', [
            'payments' => Payment::query()->with('invoice.sale.client')->latest()->get(),
            'invoices' => Invoice::query()->with('sale.client')->whereIn('status', ['unpaid', 'partial', 'overdue'])->get(),
        ]);
    }

    public function store(StorePaymentRequest $request)
    {
        $payment = Payment::query()->create($request->validated());
        $this->paymentService->refreshInvoiceStatus($payment->invoice);

        return redirect()->route('payments.index')->with('success', 'Payment recorded successfully.');
    }
}
