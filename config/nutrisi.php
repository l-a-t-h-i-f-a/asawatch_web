<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Penyedia analisis nutrisi
    |--------------------------------------------------------------------------
    |
    | 'stub' — angka tetap, dipakai pengembangan/tes tanpa memanggil apa pun.
    | 'http' — layanan vision Python (repo "Test API Food Detection"), yang
    |          memanggil Gemini lalu menghitung gizinya dari tabel TKPI.
    |
    | Bindingnya dipilih di AppServiceProvider — tidak ada tempat lain yang
    | perlu tahu penyedia mana yang sedang dipakai (bagian 6 dokumen API).
    |
    */

    'penyedia' => env('NUTRISI_PENYEDIA', 'stub'),

    'http' => [
        // Laravel berjalan di dalam container; layanan Python di host, jadi
        // alamatnya adalah gateway jaringan compose, bukan 127.0.0.1.
        'url' => env('NUTRISI_URL', 'http://192.168.80.1:8000'),

        // Panggilan Gemini pada eval berkisar 2,7–5,1 detik; 60 detik memberi
        // ruang untuk foto besar tanpa menggantung selamanya.
        'timeout' => (int) env('NUTRISI_TIMEOUT', 60),
    ],

];
