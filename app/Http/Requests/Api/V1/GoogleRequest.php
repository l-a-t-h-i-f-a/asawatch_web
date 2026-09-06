<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GoogleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Hanya bentuknya yang divalidasi di sini. Isi `id_token` -- tanda tangan,
     * `aud`, `exp` -- diperiksa AuthController lewat kunci publik Google;
     * apa pun yang dikirim ponsel bisa dikarang, jadi validasi Laravel biasa
     * tidak membuktikan apa-apa tentang siapa pemiliknya.
     */
    public function rules(): array
    {
        return [
            'id_token' => ['required', 'string'],
            'nama_perangkat' => ['required', 'string', 'max:255'],
        ];
    }
}
