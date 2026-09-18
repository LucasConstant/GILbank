<?php

namespace App\Http\Requests\AccountManager;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class StatementFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Account $account */
        $account = $this->route('account');

        return $this->user()?->can('viewStatement', $account) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
