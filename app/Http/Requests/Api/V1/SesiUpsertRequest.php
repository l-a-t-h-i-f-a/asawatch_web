<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SesiUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'waktu_foto' => ['required', 'date'],
            't0' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,menunggu_perangkat,berjalan,selesai,tidak_lengkap,dibatalkan'],
            'waktu_tidak_pasti' => ['nullable', 'boolean'],
            'sesi_uji' => ['nullable', 'boolean'],
            'diperbarui_pada' => ['nullable', 'date'],
            'dihapus_pada' => ['nullable', 'date'],

            'sampel' => ['nullable', 'array', 'max:4'],
            'sampel.*.index' => ['required_with:sampel', 'integer', 'between:0,3'],
            'sampel.*.detik_relatif_t0' => ['nullable', 'integer'],
            'sampel.*.status' => ['required_with:sampel', 'in:menunggu,terisi,terlewat'],
            'sampel.*.dari_buffer' => ['nullable', 'boolean'],
            'sampel.*.gula_darah' => ['nullable', 'integer', 'min:0'],
            'sampel.*.detak_jantung' => ['nullable', 'integer', 'min:0'],
            'sampel.*.sistolik' => ['nullable', 'integer', 'min:0'],
            'sampel.*.diastolik' => ['nullable', 'integer', 'min:0'],
            'sampel.*.spo2' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
