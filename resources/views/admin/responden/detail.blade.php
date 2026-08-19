@extends('admin.layout')

@section('title', 'Detail Responden — AsaWatch')
@section('page-title', 'Detail Responden')
@section('page-subtitle', 'Ringkasan profil, perangkat, dan data kesehatan terbaru')

@php
  $profil = $user->profil;
  $perangkat = $user->perangkat->first();
  $inisial = \Illuminate\Support\Str::of($user->nama)->explode(' ')->filter()->map(fn ($kata) => mb_substr($kata, 0, 1))->take(2)->implode('');
  $usia = $profil?->tanggal_lahir ? $profil->tanggal_lahir->age . ' tahun' : 'Belum diisi';
  $sampel = $sampelTerbaru;
  $porsiSelesai = $totalSesi ? round(($sesiSelesai / $totalSesi) * 100) : 0;
@endphp

@section('content')
  @if(isset($isAdminView) && $isAdminView)
    <a class="fw-bold text-decoration-none d-inline-flex align-items-center gap-2" href="{{ route('admin.users.index') }}" style="font-size:.84rem"><i class="bi bi-arrow-left"></i> Kembali ke daftar pengguna</a>
  @else
    <a class="fw-bold text-decoration-none d-inline-flex align-items-center gap-2" href="{{ route('admin.responden.index') }}" style="font-size:.84rem"><i class="bi bi-arrow-left"></i> Kembali ke riwayat sesi</a>
  @endif

  <div class="row g-3">
    <div class="col-12 col-xl-8">
      <div class="hw-card hw-card-pad h-100 d-flex gap-3 align-items-center flex-wrap">
        <div class="hw-avatar" style="width:82px;height:82px;flex:0 0 82px;border-radius:24px;font-size:1.6rem">{{ $inisial ?: '?' }}</div>
        <div class="flex-grow-1">
          <div style="font-size:1.42rem;font-weight:800;letter-spacing:-.5px">{{ $user->nama }}</div>
          <div class="hw-sub">{{ $user->email }}</div>
          <div class="d-flex flex-wrap gap-4 mt-3">
            <div><div class="hw-stat-label">Usia</div><div class="fw-bold mt-1" style="font-size:.9rem">{{ $usia }}</div></div>
            <div><div class="hw-stat-label">Jenis kelamin</div><div class="fw-bold mt-1" style="font-size:.9rem">{{ $profil?->jenis_kelamin ? ucfirst($profil->jenis_kelamin) : 'Belum diisi' }}</div></div>
            <div><div class="hw-stat-label">Golongan darah</div><div class="fw-bold mt-1" style="font-size:.9rem">{{ $profil?->golongan_darah ?? '—' }}</div></div>
            <div><div class="hw-stat-label">Bergabung</div><div class="fw-bold mt-1" style="font-size:.9rem">{{ $user->created_at->translatedFormat('d M Y') }}</div></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-xl-4">
      <div class="hw-card hw-card-pad h-100 d-flex flex-column gap-2" style="background:linear-gradient(160deg,#E9F8F0,#D6F0E3);border-color:#CFEBDD">
        <div class="d-flex align-items-center gap-2">
          <div class="hw-icon-box" style="width:44px;height:44px;border-radius:13px;background:var(--hw-ink);color:var(--hw-mint)"><i class="bi bi-smartwatch"></i></div>
          <div class="flex-grow-1"><div class="fw-bold" style="font-size:.9rem">{{ $perangkat ? 'Perangkat terhubung' : 'Belum ada perangkat' }}</div><div style="font-size:.78rem;color:#4A6A5C">{{ $perangkat?->nama ?? 'Hubungkan AsaWatch dari aplikasi' }}</div></div>
          @if($perangkat?->baterai_terakhir !== null)<div class="fw-bold text-ok" style="font-size:.82rem">{{ $perangkat->baterai_terakhir }}%</div>@endif
        </div>
        <div style="font-size:.78rem;color:#4A6A5C">{{ $perangkat?->terakhir_tersambung ? 'Terakhir tersambung ' . $perangkat->terakhir_tersambung->translatedFormat('d M Y, H:i') : 'Belum ada sinkronisasi perangkat.' }}</div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-sm-6 col-xxl-3"><div class="hw-card hw-card-pad h-100"><div class="d-flex gap-2 align-items-center"><i class="bi bi-heart-pulse-fill" style="color:var(--hw-red)"></i><div class="fw-semibold" style="font-size:.84rem">Detak Jantung Terakhir</div></div><div class="mt-3" style="font-size:1.7rem;font-weight:800">{{ $sampel?->detak_jantung ?? '—' }} <span style="font-size:.78rem;color:var(--hw-muted-2)">bpm</span></div></div></div>
    <div class="col-12 col-sm-6 col-xxl-3"><div class="hw-card hw-card-pad h-100"><div class="d-flex gap-2 align-items-center"><i class="bi bi-droplet-fill" style="color:var(--hw-blue)"></i><div class="fw-semibold" style="font-size:.84rem">Gula Darah Terakhir</div></div><div class="mt-3" style="font-size:1.7rem;font-weight:800">{{ $sampel?->gula_darah ?? '—' }} <span style="font-size:.78rem;color:var(--hw-muted-2)">mg/dL</span></div></div></div>
    <div class="col-12 col-sm-6 col-xxl-3"><div class="hw-card hw-card-pad h-100"><div class="d-flex gap-2 align-items-center"><i class="bi bi-activity" style="color:var(--hw-orange)"></i><div class="fw-semibold" style="font-size:.84rem">Tekanan Darah Terakhir</div></div><div class="mt-3" style="font-size:1.7rem;font-weight:800">{{ $sampel?->sistolik && $sampel?->diastolik ? "$sampel->sistolik/$sampel->diastolik" : '—' }} <span style="font-size:.78rem;color:var(--hw-muted-2)">mmHg</span></div></div></div>
    <div class="col-12 col-sm-6 col-xxl-3"><div class="hw-card hw-card-pad h-100"><div class="d-flex gap-2 align-items-center"><i class="bi bi-check2-circle" style="color:var(--hw-green-400)"></i><div class="fw-semibold" style="font-size:.84rem">Sesi Selesai</div></div><div class="mt-3" style="font-size:1.7rem;font-weight:800">{{ $porsiSelesai }}<span style="font-size:.9rem">%</span></div><div class="hw-sub">{{ $sesiSelesai }} dari {{ $totalSesi }} sesi</div></div></div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-xl-7"><div class="hw-card hw-card-pad h-100"><div class="hw-title mb-3">Profil Kesehatan</div><div class="row g-3"><div class="col-6"><div class="hw-stat-label">Tinggi badan</div><div class="fw-bold mt-1">{{ $profil?->tinggi_cm ? $profil->tinggi_cm . ' cm' : 'Belum diisi' }}</div></div><div class="col-6"><div class="hw-stat-label">Berat badan</div><div class="fw-bold mt-1">{{ $profil?->berat_kg ? $profil->berat_kg . ' kg' : 'Belum diisi' }}</div></div><div class="col-6"><div class="hw-stat-label">SpO₂ terakhir</div><div class="fw-bold mt-1">{{ $sampel?->spo2 ? $sampel->spo2 . '%' : 'Belum ada data' }}</div></div><div class="col-6"><div class="hw-stat-label">Sampel terakhir</div><div class="fw-bold mt-1">{{ $sampel?->created_at?->translatedFormat('d M Y, H:i') ?? 'Belum ada data' }}</div></div></div></div></div>
    <div class="col-12 col-xl-5">
      <div class="hw-card hw-card-pad h-100">
        <div class="hw-title mb-3">Sesi Terbaru</div>
        @forelse($sesiList->take(4) as $sesi)
          <div class="d-flex align-items-center gap-3 py-2" style="border-bottom:1px solid #F3F9F6">
            <i class="bi bi-camera-fill" style="color:var(--hw-green-400)"></i>
            <div class="flex-grow-1">
              <div class="fw-bold" style="font-size:.84rem">{{ $sesi->waktu_foto->translatedFormat('d M Y, H:i') }}</div>
              <div class="hw-sub">{{ ucfirst(str_replace('_', ' ', $sesi->status)) }}</div>
            </div>
            @if(isset($isAdminView) && $isAdminView)
              <a class="btn btn-hw-outline btn-sm" href="{{ route('admin.users.session.show', [$user, $sesi]) }}">Lihat</a>
            @else
              <a class="btn btn-hw-outline btn-sm" href="{{ route('admin.responden.show', $sesi) }}">Lihat</a>
            @endif
          </div>
        @empty
          <div class="hw-note">Belum ada sesi makan yang tercatat.</div>
        @endforelse
        @if(!isset($isAdminView) || !$isAdminView)
          <div class="mt-3">
            <a class="btn btn-hw-outline w-100" href="{{ route('admin.responden.index') }}">Lihat seluruh riwayat</a>
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
