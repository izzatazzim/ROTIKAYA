<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'salesperson_id' => ['required', 'exists:users,id'],
            'campaign_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:pending,completed,cancelled'],
            'contract' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required'      => 'Please select a customer.',
            'client_id.exists'        => 'The selected customer was not found.',
            'salesperson_id.required' => 'Please select a sales rep.',
            'salesperson_id.exists'   => 'The selected sales rep was not found.',
            'campaign_name.required'  => 'Please enter a campaign name.',
            'amount.required'         => 'Please enter the sale amount.',
            'amount.numeric'          => 'Amount must be a number, like 1500.',
            'amount.min'              => 'Amount cannot be negative.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
            'contract.mimes'          => 'The contract must be a PDF file.',
            'contract.max'            => 'The contract file must be smaller than 5MB.',
        ];
    }
}
