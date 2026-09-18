<?php

namespace App\Http\Requests\AccountManager;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class StoreLimitRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Account $account */
        $account = $this->route('account');

        return ($this->user()?->can('create', \App\Models\LimitRequest::class) ?? false)
            && ($this->user()?->can('update', $account) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Account $account */
        $account = $this->route('account');

        return [
            'limite_solicitado' => ['required', 'numeric', 'gt:'.$account->limite],
        ];
    }
}
