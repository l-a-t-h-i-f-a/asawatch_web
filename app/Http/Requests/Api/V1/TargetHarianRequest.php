<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class TargetHarianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kalori' => ['nullable', 'integer', 'between:0,10000'],
            'karbohidrat' => ['nullable', 'integer', 'between:0,2000'],
            'langkah' => ['nullable', 'integer', 'between:0,100000'],
        ];
    }
}
