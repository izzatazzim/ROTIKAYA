<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Please enter a name.',
            'email.required'    => 'Please enter an email address.',
            'email.email'       => 'That doesn\'t look like a valid email address.',
            'email.unique'      => 'Someone is already using this email. Try another one.',
            'password.required' => 'Please enter a password.',
            'password.min'      => 'Password must be at least 8 characters.',
            'role_id.required'  => 'Please select a role for this user.',
            'role_id.exists'    => 'The selected role is not valid.',
        ];
    }
}
