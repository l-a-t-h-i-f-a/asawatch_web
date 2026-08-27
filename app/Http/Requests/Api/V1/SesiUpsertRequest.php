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

            // Hasil deteksi ikut disinkronkan supaya sesi yang diunduh di
            // perangkat lain tetap membawa kartu gizinya (bagian 5.2).
            'hasil' => ['nullable', 'array'],
            'hasil.indeks_glikemik_perkiraan' => ['nullable', 'string', 'max:32'],
            'hasil.keyakinan' => ['nullable', 'numeric', 'between:0,1'],
            'hasil.dikoreksi_user' => ['nullable', 'boolean'],
            'hasil.total' => ['nullable', 'array'],
            'hasil.total.kalori' => ['nullable', 'numeric', 'min:0'],
            'hasil.total.karbohidrat' => ['nullable', 'numeric', 'min:0'],
            'hasil.total.protein' => ['nullable', 'numeric', 'min:0'],
            'hasil.total.lemak' => ['nullable', 'numeric', 'min:0'],
            'hasil.total.gula_total' => ['nullable', 'numeric', 'min:0'],
            'hasil.total.serat' => ['nullable', 'numeric', 'min:0'],
            'hasil.zat_tidak_lengkap' => ['nullable', 'array'],
            'hasil.zat_tidak_lengkap.*' => ['string', 'max:32'],
            'hasil.makanan' => ['nullable', 'array'],
            'hasil.makanan.*.urutan' => ['nullable', 'integer', 'min:0'],
            'hasil.makanan.*.nama' => ['required_with:hasil.makanan', 'string', 'max:255'],
            'hasil.makanan.*.porsi' => ['nullable', 'string', 'max:255'],
            'hasil.makanan.*.estimasi_gram' => ['nullable', 'numeric', 'min:0'],
            'hasil.makanan.*.nutrisi' => ['nullable', 'array'],
            'hasil.makanan.*.nutrisi.kalori' => ['nullable', 'numeric', 'min:0'],
            'hasil.makanan.*.nutrisi.karbohidrat' => ['nullable', 'numeric', 'min:0'],
            'hasil.makanan.*.nutrisi.protein' => ['nullable', 'numeric', 'min:0'],
            'hasil.makanan.*.nutrisi.lemak' => ['nullable', 'numeric', 'min:0'],
            'hasil.makanan.*.nutrisi.gula_total' => ['nullable', 'numeric', 'min:0'],
            'hasil.makanan.*.nutrisi.serat' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
