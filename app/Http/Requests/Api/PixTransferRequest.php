<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PixTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'conta_destino_id' => ['required', 'integer', 'exists:contas,id'],
            'valor' => ['required', 'numeric', 'gt:0'],
            'descricao' => ['nullable', 'string', 'max:255'],
        ];
    }
}
