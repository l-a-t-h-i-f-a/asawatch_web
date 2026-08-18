<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class KalibrasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'waktu' => ['required', 'date'],
            'sistolik_referensi' => ['required', 'integer', 'between:50,300'],
            'diastolik_referensi' => ['required', 'integer', 'between:30,200'],
            'sistolik_jam' => ['required', 'integer', 'between:50,300'],
            'diastolik_jam' => ['required', 'integer', 'between:30,200'],
        ];
    }
}
