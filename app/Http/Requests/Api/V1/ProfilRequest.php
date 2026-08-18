<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_lahir' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'jenis_kelamin' => ['nullable', 'in:laki-laki,perempuan'],
            'golongan_darah' => ['nullable', 'in:A,B,AB,O'],
            'tinggi_cm' => ['nullable', 'numeric', 'between:50,250'],
            'berat_kg' => ['nullable', 'numeric', 'between:2,400'],
        ];
    }
}
