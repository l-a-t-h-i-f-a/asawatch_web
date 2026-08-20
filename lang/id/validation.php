<?php

/**
 * Pesan validasi Bahasa Indonesia.
 *
 * APP_LOCALE dan APP_FALLBACK_LOCALE keduanya 'id', jadi tanpa berkas ini
 * Laravel tidak punya tempat untuk mundur dan mengembalikan kunci mentah
 * ("validation.unique") apa adanya. Isi `galat.pesan` ditampilkan langsung
 * ke pengguna aplikasi (bagian 3.1 dokumen API), jadi kalimatnya harus bisa
 * dibaca orang awam — bukan istilah teknis.
 */
return [

    'accepted' => 'Kolom :attribute harus disetujui.',
    'accepted_if' => 'Kolom :attribute harus disetujui jika :other bernilai :value.',
    'active_url' => 'Kolom :attribute bukan URL yang valid.',
    'after' => 'Kolom :attribute harus berisi tanggal setelah :date.',
    'after_or_equal' => 'Kolom :attribute harus berisi tanggal setelah atau sama dengan :date.',
    'alpha' => 'Kolom :attribute hanya boleh berisi huruf.',
    'alpha_dash' => 'Kolom :attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => 'Kolom :attribute hanya boleh berisi huruf dan angka.',
    'any_of' => 'Kolom :attribute tidak valid.',
    'array' => 'Kolom :attribute harus berupa daftar.',
    'ascii' => 'Kolom :attribute hanya boleh berisi karakter dan simbol satu byte.',
    'before' => 'Kolom :attribute harus berisi tanggal sebelum :date.',
    'before_or_equal' => 'Kolom :attribute harus berisi tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => 'Kolom :attribute harus berisi antara :min sampai :max item.',
        'file' => 'Berkas :attribute harus berukuran antara :min sampai :max kilobyte.',
        'numeric' => 'Kolom :attribute harus bernilai antara :min sampai :max.',
        'string' => 'Kolom :attribute harus terdiri dari :min sampai :max karakter.',
    ],
    'boolean' => 'Kolom :attribute harus bernilai benar atau salah.',
    'can' => 'Kolom :attribute berisi nilai yang tidak diizinkan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'contains' => 'Kolom :attribute tidak memuat nilai yang diwajibkan.',
    'current_password' => 'Kata sandi salah.',
    'date' => 'Kolom :attribute bukan tanggal yang valid.',
    'date_equals' => 'Kolom :attribute harus berisi tanggal yang sama dengan :date.',
    'date_format' => 'Format kolom :attribute tidak sesuai dengan :format.',
    'decimal' => 'Kolom :attribute harus memiliki :decimal angka di belakang koma.',
    'declined' => 'Kolom :attribute harus ditolak.',
    'declined_if' => 'Kolom :attribute harus ditolak jika :other bernilai :value.',
    'different' => 'Kolom :attribute dan :other harus berbeda.',
    'digits' => 'Kolom :attribute harus terdiri dari :digits angka.',
    'digits_between' => 'Kolom :attribute harus terdiri dari :min sampai :max angka.',
    'dimensions' => 'Dimensi gambar :attribute tidak sesuai.',
    'distinct' => 'Kolom :attribute berisi nilai yang terduplikat.',
    'doesnt_contain' => 'Kolom :attribute tidak boleh memuat nilai tersebut.',
    'doesnt_end_with' => 'Kolom :attribute tidak boleh diakhiri dengan salah satu dari: :values.',
    'doesnt_start_with' => 'Kolom :attribute tidak boleh diawali dengan salah satu dari: :values.',
    'email' => 'Kolom :attribute harus berupa alamat email yang valid.',
    'encoding' => 'Kolom :attribute memakai pengodean karakter yang tidak didukung.',
    'ends_with' => 'Kolom :attribute harus diakhiri dengan salah satu dari: :values.',
    'enum' => 'Pilihan :attribute tidak valid.',
    'exists' => 'Pilihan :attribute tidak valid.',
    'extensions' => 'Berkas :attribute harus berekstensi salah satu dari: :values.',
    'file' => 'Kolom :attribute harus berupa berkas.',
    'filled' => 'Kolom :attribute wajib diisi.',
    'gt' => [
        'array' => 'Kolom :attribute harus berisi lebih dari :value item.',
        'file' => 'Berkas :attribute harus lebih besar dari :value kilobyte.',
        'numeric' => 'Kolom :attribute harus bernilai lebih besar dari :value.',
        'string' => 'Kolom :attribute harus terdiri dari lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => 'Kolom :attribute harus berisi :value item atau lebih.',
        'file' => 'Berkas :attribute harus berukuran :value kilobyte atau lebih.',
        'numeric' => 'Kolom :attribute harus bernilai :value atau lebih.',
        'string' => 'Kolom :attribute harus terdiri dari :value karakter atau lebih.',
    ],
    'hex_color' => 'Kolom :attribute harus berupa kode warna heksadesimal yang valid.',
    'image' => 'Kolom :attribute harus berupa gambar.',
    'in' => 'Pilihan :attribute tidak valid.',
    'in_array' => 'Kolom :attribute tidak ada di dalam :other.',
    'in_array_keys' => 'Kolom :attribute harus memuat setidaknya satu dari kunci berikut: :values.',
    'integer' => 'Kolom :attribute harus berupa bilangan bulat.',
    'ip' => 'Kolom :attribute harus berupa alamat IP yang valid.',
    'ipv4' => 'Kolom :attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => 'Kolom :attribute harus berupa alamat IPv6 yang valid.',
    'json' => 'Kolom :attribute harus berupa JSON yang valid.',
    'list' => 'Kolom :attribute harus berupa daftar.',
    'lowercase' => 'Kolom :attribute harus ditulis dengan huruf kecil.',
    'lt' => [
        'array' => 'Kolom :attribute harus berisi kurang dari :value item.',
        'file' => 'Berkas :attribute harus lebih kecil dari :value kilobyte.',
        'numeric' => 'Kolom :attribute harus bernilai lebih kecil dari :value.',
        'string' => 'Kolom :attribute harus terdiri dari kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => 'Kolom :attribute tidak boleh berisi lebih dari :value item.',
        'file' => 'Berkas :attribute harus berukuran :value kilobyte atau kurang.',
        'numeric' => 'Kolom :attribute harus bernilai :value atau kurang.',
        'string' => 'Kolom :attribute harus terdiri dari :value karakter atau kurang.',
    ],
    'mac_address' => 'Kolom :attribute harus berupa alamat MAC yang valid.',
    'max' => [
        'array' => 'Kolom :attribute tidak boleh berisi lebih dari :max item.',
        'file' => 'Berkas :attribute tidak boleh lebih besar dari :max kilobyte.',
        'numeric' => 'Kolom :attribute tidak boleh bernilai lebih dari :max.',
        'string' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
    ],
    'max_digits' => 'Kolom :attribute tidak boleh terdiri dari lebih dari :max angka.',
    'mimes' => 'Kolom :attribute harus berupa berkas bertipe: :values.',
    'mimetypes' => 'Kolom :attribute harus berupa berkas bertipe: :values.',
    'min' => [
        'array' => 'Kolom :attribute harus berisi minimal :min item.',
        'file' => 'Berkas :attribute harus berukuran minimal :min kilobyte.',
        'numeric' => 'Kolom :attribute harus bernilai minimal :min.',
        'string' => 'Kolom :attribute harus terdiri dari minimal :min karakter.',
    ],
    'min_digits' => 'Kolom :attribute harus terdiri dari minimal :min angka.',
    'missing' => 'Kolom :attribute tidak boleh dikirim.',
    'missing_if' => 'Kolom :attribute tidak boleh dikirim jika :other bernilai :value.',
    'missing_unless' => 'Kolom :attribute tidak boleh dikirim kecuali :other bernilai :value.',
    'missing_with' => 'Kolom :attribute tidak boleh dikirim bersama :values.',
    'missing_with_all' => 'Kolom :attribute tidak boleh dikirim bersama :values.',
    'multiple_of' => 'Kolom :attribute harus merupakan kelipatan dari :value.',
    'not_in' => 'Pilihan :attribute tidak valid.',
    'not_regex' => 'Format kolom :attribute tidak valid.',
    'numeric' => 'Kolom :attribute harus berupa angka.',
    'password' => [
        'letters' => 'Kata sandi harus memuat setidaknya satu huruf.',
        'mixed' => 'Kata sandi harus memuat setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers' => 'Kata sandi harus memuat setidaknya satu angka.',
        'symbols' => 'Kata sandi harus memuat setidaknya satu simbol.',
        'uncompromised' => 'Kata sandi ini pernah bocor di internet. Pilih kata sandi lain.',
    ],
    'present' => 'Kolom :attribute wajib dikirim.',
    'present_if' => 'Kolom :attribute wajib dikirim jika :other bernilai :value.',
    'present_unless' => 'Kolom :attribute wajib dikirim kecuali :other bernilai :value.',
    'present_with' => 'Kolom :attribute wajib dikirim bersama :values.',
    'present_with_all' => 'Kolom :attribute wajib dikirim bersama :values.',
    'prohibited' => 'Kolom :attribute tidak diizinkan.',
    'prohibited_if' => 'Kolom :attribute tidak diizinkan jika :other bernilai :value.',
    'prohibited_if_accepted' => 'Kolom :attribute tidak diizinkan jika :other disetujui.',
    'prohibited_if_declined' => 'Kolom :attribute tidak diizinkan jika :other ditolak.',
    'prohibited_unless' => 'Kolom :attribute tidak diizinkan kecuali :other ada di dalam :values.',
    'prohibits' => 'Kolom :attribute membuat :other tidak boleh dikirim.',
    'regex' => 'Format kolom :attribute tidak valid.',
    'required' => 'Kolom :attribute wajib diisi.',
    'required_array_keys' => 'Kolom :attribute wajib memuat entri untuk: :values.',
    'required_if' => 'Kolom :attribute wajib diisi jika :other bernilai :value.',
    'required_if_accepted' => 'Kolom :attribute wajib diisi jika :other disetujui.',
    'required_if_declined' => 'Kolom :attribute wajib diisi jika :other ditolak.',
    'required_unless' => 'Kolom :attribute wajib diisi kecuali :other ada di dalam :values.',
    'required_with' => 'Kolom :attribute wajib diisi bila ada :values.',
    'required_with_all' => 'Kolom :attribute wajib diisi bila ada :values.',
    'required_without' => 'Kolom :attribute wajib diisi bila tidak ada :values.',
    'required_without_all' => 'Kolom :attribute wajib diisi bila tidak ada satu pun dari :values.',
    'same' => 'Kolom :attribute dan :other harus sama.',
    'size' => [
        'array' => 'Kolom :attribute harus berisi :size item.',
        'file' => 'Berkas :attribute harus berukuran :size kilobyte.',
        'numeric' => 'Kolom :attribute harus bernilai :size.',
        'string' => 'Kolom :attribute harus terdiri dari :size karakter.',
    ],
    'starts_with' => 'Kolom :attribute harus diawali dengan salah satu dari: :values.',
    'string' => 'Kolom :attribute harus berupa teks.',
    'timezone' => 'Kolom :attribute harus berupa zona waktu yang valid.',
    'unique' => ':attribute ini sudah terdaftar.',
    'uploaded' => 'Berkas :attribute gagal diunggah.',
    'uppercase' => 'Kolom :attribute harus ditulis dengan huruf besar.',
    'url' => 'Format :attribute tidak valid.',
    'ulid' => 'Kolom :attribute harus berupa ULID yang valid.',
    'uuid' => 'Kolom :attribute harus berupa UUID yang valid.',

    /*
    |---------------------------------------------------------------------------
    | Pesan khusus per kolom
    |---------------------------------------------------------------------------
    |
    | Dipakai ketika kalimat generik di atas terasa kaku untuk pengguna lanjut
    | usia — di sini pesannya ditulis ulang penuh, bukan sekadar menukar nama
    | kolom.
    |
    */

    'custom' => [
        'email' => [
            'unique' => 'Email ini sudah terdaftar. Silakan masuk atau pakai email lain.',
            'email' => 'Alamat email tidak valid. Contoh: nama@email.com',
            'required' => 'Email wajib diisi.',
        ],
        'kata_sandi' => [
            'required' => 'Kata sandi wajib diisi.',
            'min' => 'Kata sandi minimal :min karakter.',
        ],
        'nama' => [
            'required' => 'Nama wajib diisi.',
        ],
        'nama_perangkat' => [
            'required' => 'Nama perangkat wajib diisi.',
        ],
        'token' => [
            'required' => 'Token atur ulang sandi wajib diisi.',
        ],
        'foto' => [
            'required' => 'Foto wajib dipilih.',
            'image' => 'Berkas yang diunggah harus berupa foto.',
            'max' => 'Ukuran foto tidak boleh lebih dari :max kilobyte.',
        ],
    ],

    /*
    |---------------------------------------------------------------------------
    | Nama kolom yang dibaca manusia
    |---------------------------------------------------------------------------
    |
    | Menggantikan nama kolom mentah (mis. "detik_relatif_t0") di dalam pesan.
    |
    */

    'attributes' => [
        'nama' => 'Nama',
        'email' => 'Email',
        'kata_sandi' => 'Kata sandi',
        'kata_sandi_lama' => 'Kata sandi lama',
        'nama_perangkat' => 'Nama perangkat',
        'token' => 'Token',
        'foto' => 'Foto',

        'tanggal_lahir' => 'Tanggal lahir',
        'jenis_kelamin' => 'Jenis kelamin',
        'tinggi_badan' => 'Tinggi badan',
        'berat_badan' => 'Berat badan',
        'nomor_telepon' => 'Nomor telepon',
        'kontak_darurat' => 'Kontak darurat',

        'waktu_foto' => 'Waktu foto',
        't0' => 'Waktu selesai makan',
        'status' => 'Status',
        'waktu_tidak_pasti' => 'Penanda waktu tidak pasti',
        'sesi_uji' => 'Penanda sesi uji',
        'diperbarui_pada' => 'Waktu pembaruan',
        'dihapus_pada' => 'Waktu penghapusan',

        'sampel' => 'Daftar sampel',
        'sampel.*.index' => 'Urutan sampel',
        'sampel.*.detik_relatif_t0' => 'Selisih detik dari waktu selesai makan',
        'sampel.*.status' => 'Status sampel',
        'sampel.*.dari_buffer' => 'Penanda data dari buffer',
        'sampel.*.gula_darah' => 'Gula darah',
        'sampel.*.detak_jantung' => 'Detak jantung',
        'sampel.*.sistolik' => 'Tekanan darah sistolik',
        'sampel.*.diastolik' => 'Tekanan darah diastolik',
        'sampel.*.spo2' => 'Kadar oksigen (SpO2)',

        'gula_darah' => 'Gula darah',
        'detak_jantung' => 'Detak jantung',
        'sistolik' => 'Tekanan darah sistolik',
        'diastolik' => 'Tekanan darah diastolik',
        'spo2' => 'Kadar oksigen (SpO2)',

        'waktu' => 'Waktu',
        'nilai_referensi' => 'Nilai referensi',
        'nilai_perangkat' => 'Nilai perangkat',
        'jenis' => 'Jenis',
        'merek' => 'Merek',
        'model' => 'Model',
        'terakhir_tersambung' => 'Terakhir tersambung',

        'target_gula_puasa' => 'Target gula darah puasa',
        'target_gula_setelah_makan' => 'Target gula darah setelah makan',
        'target_langkah' => 'Target langkah',
        'target_kalori' => 'Target kalori',
    ],

];
