@extends('admin.layout')

@section('title', 'Ekspor Data Saya · AsaWatch')
@section('page-title', 'Ekspor Data Saya')
@section('page-subtitle', 'Unduh seluruh datamu sendiri — hak pengguna, bukan fitur tambahan')

@section('content')

  <div class="row g-3 align-items-start">
    <div class="col-12 col-xl-7">
      <div class="hw-card hw-card-pad d-flex flex-column gap-4">
        <div>
          <div class="hw-title">Unduh Data</div>
          <div class="hw-sub">Berisi profil, seluruh sesi &amp; pengukuran, riwayat kalibrasi, dan daftar perangkat</div>
        </div>

        <div class="d-flex flex-wrap gap-3">
          <a class="btn btn-hw d-flex align-items-center gap-2" style="padding:.85rem 1.5rem;font-size:.95rem" href="{{ route('admin.ekspor.json') }}">
            <i class="bi bi-filetype-json"></i> Unduh JSON Lengkap
          </a>
          <a class="btn btn-hw-outline d-flex align-items-center gap-2" style="padding:.85rem 1.5rem;font-size:.95rem" href="{{ route('admin.ekspor.csv') }}">
            <i class="bi bi-filetype-csv"></i> Unduh CSV Pengukuran
          </a>
        </div>

        <div class="hw-note d-flex gap-2">
          <i class="bi bi-info-circle" style="color:var(--hw-green-400)"></i>
          <div>JSON berisi struktur lengkap (profil, sesi, hasil analisis nutrisi, kalibrasi, perangkat). CSV hanya berisi baris pengukuran per titik sesi, cocok untuk dibuka di Excel/SPSS.</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-5">
      <div class="hw-card hw-card-pad">
        <div class="hw-title mb-3">Ringkasan Data</div>
        <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #F3F9F6">
          <span style="font-size:.84rem;color:var(--hw-ink-2)">Total sesi</span>
          <span class="fw-bold" style="font-size:.84rem">{{ $totalSesi }}</span>
        </div>
        <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #F3F9F6">
          <span style="font-size:.84rem;color:var(--hw-ink-2)">Riwayat kalibrasi</span>
          <span class="fw-bold" style="font-size:.84rem">{{ $totalKalibrasi }}</span>
        </div>
        <div class="d-flex justify-content-between py-2">
          <span style="font-size:.84rem;color:var(--hw-ink-2)">Perangkat terdaftar</span>
          <span class="fw-bold" style="font-size:.84rem">{{ $totalPerangkat }}</span>
        </div>
      </div>
    </div>
  </div>

@endsection
