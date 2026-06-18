<?php

namespace App\Http\Requests;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DashboardFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $start = $this->input('start') ?: Carbon::now()->startOfMonth()->toDateString();
        $end = $this->input('end') ?: Carbon::now()->endOfMonth()->toDateString();

        $this->merge([
            'start' => $start,
            'end' => $end,
            'client_id' => $this->input('client_id') ?: null,
            'salesperson_id' => $this->input('salesperson_id') ?: null,
        ]);

        $user = Auth::user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('sales_staff')) {
            // Enforce self-scoping even if salesperson_id is tampered in URL.
            $this->merge(['salesperson_id' => $user->id]);
        }
    }

    public function rules(): array
    {
        return [
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'salesperson_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $salespersonId = $this->input('salesperson_id');
            if (! $salespersonId) {
                return;
            }

            $isSalesStaff = User::query()
                ->whereKey((int) $salespersonId)
                ->whereHas('role', fn ($query) => $query->where('name', 'sales_staff'))
                ->exists();

            if (! $isSalesStaff) {
                $validator->errors()->add('salesperson_id', 'The selected salesperson must have sales_staff role.');
            }
        });
    }
}
