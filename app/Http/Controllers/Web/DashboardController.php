<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardFilterRequest;
use App\Models\Client;
use App\Models\User;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function index(DashboardFilterRequest $request)
    {
        $user = $request->user();
        $filters = $request->validated();

        // Sales staff is always scoped to self regardless of URL tampering.
        if ($user->hasRole('sales_staff')) {
            $filters['salesperson_id'] = $user->id;
        }

        $stats = $this->dashboardService->getStats($filters, $user);
        $chartData = $this->dashboardService->getChartData($filters, $user);
        $recentPayments = $this->dashboardService->getRecentPayments($filters, $user);

        $isDefaultRange = $filters['start'] === now()->startOfMonth()->toDateString()
            && $filters['end'] === now()->endOfMonth()->toDateString();

        $viewData = [
            'stats' => $stats,
            'chartData' => $chartData,
            'recentPayments' => $recentPayments,
            'filters' => $filters,
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
            'salespeople' => User::query()
                ->whereHas('role', fn ($query) => $query->where('name', 'sales_staff'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'showSalespersonFilter' => ! $user->hasRole('sales_staff'),
            'isDefaultRange' => $isDefaultRange,
            'isSalesRole' => $user->hasRole('sales_staff'),
        ];

        if ($user->hasRole('sales_staff')) {
            return view('dashboard.sales', $viewData);
        }

        if ($user->hasRole('accountant')) {
            return view('dashboard.accountant', $viewData);
        }

        $viewData['usersCount'] = User::query()->count();
        return view('dashboard.admin', $viewData);
    }
}
