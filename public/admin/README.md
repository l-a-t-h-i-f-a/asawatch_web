# AsaWatch Admin — Template Bootstrap 5

Template statis untuk laman admin/peneliti aplikasi AsaWatch (jam tangan kesehatan lansia).

## Struktur
    bootstrap/
      login.html          Halaman masuk (submit -> dashboard.html)
      dashboard.html      Ringkasan KPI, tren agregat, tabel "perlu perhatian"
      responden.html      Tabel daftar responden + filter & paginasi
      detail.html         Profil satu responden, metrik, grafik, catatan makanan
      analitik.html       Statistik agregat, korelasi, ringkasan temuan
      ekspor.html         Penyusun dataset + privasi + riwayat ekspor
      assets/css/asawatch.css   Tema (variabel warna, komponen .hw-*)
      assets/js/asawatch.js     Segmented control, grafik responsif, toggle sandi

## Dependensi
Bootstrap 5.3.3, Bootstrap Icons 1.11.3, font Plus Jakarta Sans — semuanya via CDN.
Untuk offline, unduh ketiganya ke `assets/` dan ganti tautan di <head>.

## Cara pakai
1. Buka `login.html` di browser (tidak perlu server).
2. Semua data masih dummy dan ditulis langsung di HTML.
3. Menghubungkan ke backend:
   - Sidebar & header identik di 5 halaman — jadikan partial (`_sidebar.php`,
     Blade `@include`, Django `{% include %}`) lalu tandai menu aktif dengan
     class `active` pada `.hw-nav .nav-link`. Sidebar dipakai dua kali:
     versi desktop (`.hw-sidebar`) dan versi mobile di dalam `#hwNav`
     (Bootstrap offcanvas, dibuka tombol `.hw-burger` di header).
   - Tabel: ulangi `<tr>` dari loop server. Kelas status: `hw-pill-ok`,
     `hw-pill-warn`, `hw-pill-bad`.
   - Grafik: SVG digambar oleh `drawChart()` di asawatch.js. Ganti objek
     `SERIES` dengan data dari API (mis. `fetch('/api/tren')`), atau tukar ke
     Chart.js bila butuh tooltip dan interaksi lebih kaya.

## Konvensi tema
Warna, radius, dan tipografi diatur lewat variabel di `:root` (`--hw-green`,
`--hw-ink`, `--hw-line`, dst.) dan override variabel Bootstrap (`--bs-primary`,
`--bs-body-font-family`). Ubah di satu tempat, seluruh halaman ikut.
