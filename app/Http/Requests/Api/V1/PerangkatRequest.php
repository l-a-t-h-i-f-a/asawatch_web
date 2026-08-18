<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class PerangkatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'terakhir_tersambung' => ['nullable', 'date'],
            'firmware' => ['nullable', 'string', 'max:100'],
            'baterai_terakhir' => ['nullable', 'integer', 'between:0,100'],
        ];
    }
}
