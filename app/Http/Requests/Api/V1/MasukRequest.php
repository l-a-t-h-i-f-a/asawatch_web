<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class MasukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'kata_sandi' => ['required', 'string'],
            'nama_perangkat' => ['required', 'string', 'max:255'],
        ];
    }
}
