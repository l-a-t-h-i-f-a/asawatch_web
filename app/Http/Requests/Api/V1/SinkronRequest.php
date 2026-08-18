<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SinkronRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => ['required', 'array'],
            'data.sesi' => ['nullable', 'array'],
            'data.sesi.*.id' => ['required', 'uuid'],
            'data.sesi.*.waktu_foto' => ['required', 'date'],
            'data.sesi.*.t0' => ['nullable', 'date'],
            'data.sesi.*.status' => ['required', 'in:draft,menunggu_perangkat,berjalan,selesai,tidak_lengkap,dibatalkan'],
            'data.sesi.*.waktu_tidak_pasti' => ['nullable', 'boolean'],
            'data.sesi.*.diperbarui_pada' => ['nullable', 'date'],
            'data.sesi.*.dihapus_pada' => ['nullable', 'date'],
            'data.sesi.*.sampel' => ['nullable', 'array', 'max:4'],
            'data.sesi.*.sampel.*.index' => ['required_with:data.sesi.*.sampel', 'integer', 'between:0,3'],
            'data.sesi.*.sampel.*.detik_relatif_t0' => ['nullable', 'integer'],
            'data.sesi.*.sampel.*.status' => ['required_with:data.sesi.*.sampel', 'in:menunggu,terisi,terlewat'],
            'data.sesi.*.sampel.*.dari_buffer' => ['nullable', 'boolean'],
            'data.sesi.*.sampel.*.gula_darah' => ['nullable', 'integer', 'min:0'],
            'data.sesi.*.sampel.*.detak_jantung' => ['nullable', 'integer', 'min:0'],
            'data.sesi.*.sampel.*.sistolik' => ['nullable', 'integer', 'min:0'],
            'data.sesi.*.sampel.*.diastolik' => ['nullable', 'integer', 'min:0'],
            'data.sesi.*.sampel.*.spo2' => ['nullable', 'integer', 'min:0', 'max:100'],

            'data.kalibrasi' => ['nullable', 'array'],
            'data.kalibrasi.*.waktu' => ['required', 'date'],
            'data.kalibrasi.*.sistolik_referensi' => ['required', 'integer', 'between:50,300'],
            'data.kalibrasi.*.diastolik_referensi' => ['required', 'integer', 'between:30,200'],
            'data.kalibrasi.*.sistolik_jam' => ['required', 'integer', 'between:50,300'],
            'data.kalibrasi.*.diastolik_jam' => ['required', 'integer', 'between:30,200'],

            'data.profil' => ['nullable', 'array'],
            'data.profil.tanggal_lahir' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'data.profil.jenis_kelamin' => ['nullable', 'in:laki-laki,perempuan'],
            'data.profil.golongan_darah' => ['nullable', 'in:A,B,AB,O'],
            'data.profil.tinggi_cm' => ['nullable', 'numeric', 'between:50,250'],
            'data.profil.berat_kg' => ['nullable', 'numeric', 'between:2,400'],
        ];
    }
}
