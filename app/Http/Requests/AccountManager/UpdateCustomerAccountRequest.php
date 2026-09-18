<?php

namespace App\Http\Requests\AccountManager;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Account $account */
        $account = $this->route('account');

        return $this->user()?->can('update', $account) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Account $account */
        $account = $this->route('account');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($account->user_id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'saldo' => ['required', 'numeric', 'min:0'],
            'limite' => ['required', 'numeric', 'min:0'],
        ];
    }
}
