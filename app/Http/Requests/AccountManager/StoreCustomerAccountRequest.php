<?php

namespace App\Http\Requests\AccountManager;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Account::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8'],
            'saldo' => ['required', 'numeric', 'min:0'],
            'limite' => ['required', 'numeric', 'min:0'],
        ];
    }
}
