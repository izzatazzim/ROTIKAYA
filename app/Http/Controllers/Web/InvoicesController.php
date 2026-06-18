<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\TemplateNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Sale;
use App\Services\InvoiceDispatchService;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvoicesController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly InvoiceDispatchService $invoiceDispatchService
    ) {
    }

    public function index()
    {
        $user = auth()->user();
        $status = request('status');
        $search = (string) request('search', '');
        $fromDate = request('from_date');
        $toDate = request('to_date');
        $customer = (string) request('customer', '');

        $query = Invoice::query()->with(['sale.client', 'lastSuccessfulDispatch'])->latest();

        if ($status === 'coming_due') {
            $query->where('status', 'unpaid')->whereDate('due_date', '>=', now())->whereDate('due_date', '<=', now()->addDays(7));
        } elseif ($status === 'outstanding') {
            $query->whereIn('status', ['unpaid', 'partial']);
        } elseif ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        if ($search !== '') {
            $query->where('invoice_number', 'like', "%{$search}%");
        }
        if ($fromDate) {
            $query->whereDate('issue_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('issue_date', '<=', $toDate);
        }
        if ($customer !== '') {
            $query->whereHas('sale.client', fn ($clientQuery) => $clientQuery->where('name', 'like', "%{$customer}%"));
        }

        if ($user?->hasRole('sales_staff')) {
            $query->whereHas('sale', fn ($saleQuery) => $saleQuery->where('salesperson_id', $user->id));
        }

        $completedSalesQuery = Sale::query()
            ->where('status', 'completed')
            ->doesntHave('invoice')
            ->with('client');

        if ($user?->hasRole('sales_staff')) {
            $completedSalesQuery->where('salesperson_id', $user->id);
        }

        return view('invoices.index', [
            'invoices' => $query->paginate(10)->withQueryString(),
            'completedSales' => $completedSalesQuery->get(),
            'status' => $status ?? 'all',
        ]);
    }

    public function store()
    {
        request()->validate(['sale_id' => ['required', 'exists:sales,id']]);
        $sale = Sale::query()->findOrFail((int) request('sale_id'));

        try {
            $this->invoiceService->generateFromSale($sale);
        } catch (TemplateNotFoundException $exception) {
            return back()->withErrors(['invoice_template' => $exception->getMessage()]);
        }

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $user = auth()->user();
        if ($user?->hasRole('sales_staff') && (int) $invoice->sale?->salesperson_id !== (int) $user->id) {
            abort(403);
        }

        $invoice->load(['sale.client', 'sale.salesperson', 'lastSuccessfulDispatch', 'dispatches.dispatcher']);

        return view('invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    public function send(Request $request, Invoice $invoice): JsonResponse|Response
    {
        $result = $this->invoiceDispatchService->dispatch($invoice, $request->user());
        $invoice->load('lastSuccessfulDispatch');

        if ($result['success']) {
            if (! $request->expectsJson()) {
                return back()->with('success', 'Invoice sent successfully.');
            }

            return response()->json([
                'ok' => true,
                'status' => 'sent',
                'channel' => $result['channel'],
                'last_sent_at' => optional($invoice->lastSuccessfulDispatch?->dispatched_at)->toDateTimeString(),
            ]);
        }

        if (! $request->expectsJson()) {
            return back()->with('error', $result['error'] ?? 'Dispatch failed.');
        }

        return response()->json([
            'ok' => false,
            'status' => 'failed',
            'error' => $result['error'] ?? 'Dispatch failed.',
        ], 422);
    }
}
