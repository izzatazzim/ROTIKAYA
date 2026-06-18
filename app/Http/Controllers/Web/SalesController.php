<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Client;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class SalesController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $search = (string) request('search', '');
        $clientSearch = (string) request('client_search', '');

        $salesQuery = Sale::query()->with(['client', 'salesperson', 'invoice'])->latest();
        if ($user?->hasRole('sales_staff')) {
            $salesQuery->where('salesperson_id', $user->id);
        }
        if ($search !== '') {
            $salesQuery->where(function ($query) use ($search): void {
                $query->where('campaign_name', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($clientQuery) => $clientQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $clientsQuery = Client::query()->orderBy('name');
        if ($clientSearch !== '') {
            $clientsQuery->where('name', 'like', "%{$clientSearch}%");
        }

        return view('sales.index', [
            'sales' => $salesQuery->paginate(8)->withQueryString(),
            'clients' => $clientsQuery->limit(30)->get(),
            'salespeople' => User::query()->whereHas('role', fn ($query) => $query->where('name', 'sales_staff'))->get(),
            'search' => $search,
            'clientSearch' => $clientSearch,
        ]);
    }

    public function clients()
    {
        $query = Client::query()->orderBy('name');
        $search = (string) request('search', '');

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        return view('clients.index', [
            'clients' => $query->get(),
            'search' => $search,
        ]);
    }

    public function store(StoreSaleRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('contract')) {
            $data['contract_path'] = $request->file('contract')->store('contracts', 'public');
        }

        unset($data['contract']);

        Sale::query()->create($data);

        return redirect()->route('sales.index')->with('success', 'Sale created successfully.');
    }

    public function storeClient(StoreClientRequest $request)
    {
        Client::query()->create($request->validated());
        return redirect()->route('sales.index')->with('success', 'Customer added successfully.');
    }
}
