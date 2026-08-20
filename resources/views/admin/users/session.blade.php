@extends('admin.layout')

@section('title', 'Detail Sesi · AsaWatch')
@section('page-title', 'Detail Sesi')
@section('page-subtitle', 'Empat titik pengukuran dan hasil analisis nutrisi satu sesi makan')

@php
  $statusLabel = fn ($status) => match ($status) {
      'draft' => 'Draf',
      'menunggu_perangkat' => 'Menunggu Perangkat',
      'berjalan' => 'Berjalan',
      'selesai' => 'Selesai',
      'tidak_lengkap' => 'Tidak Lengkap',
      'dibatalkan' => 'Dibatalkan',
      default => ucfirst($status),
  };
  $labelTitik = ['Baseline', 'Selesai Makan (t0)', '+1 Jam', '+2 Jam'];
@endphp

@section('content')

  <a class="fw-bold text-decoration-none d-inline-flex align-items-center gap-2" href="{{ route('admin.users.show', $selectedUser) }}" style="font-size:.84rem"><i class="bi bi-arrow-left"></i> Kembali ke detail responden</a>

  <div class="hw-card hw-card-pad d-flex gap-3 align-items-center flex-wrap">
    <div class="hw-icon-box" style="width:64px;height:64px;border-radius:20px;font-size:1.4rem;background:var(--hw-tint);color:var(--hw-green-600)"><i class="bi bi-camera-fill"></i></div>
    <div class="flex-grow-1">
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <div style="font-size:1.3rem;font-weight:800;letter-spacing:-.4px">{{ $sesi->waktu_foto->translatedFormat('d M Y, H:i') }}</div>
        <span class="hw-pill hw-pill-info">{{ $statusLabel($sesi->status) }}</span>
        @if ($sesi->waktu_tidak_pasti)
          <span class="hw-pill hw-pill-warn">Waktu tidak pasti</span>
        @endif
      </div>
      <div class="hw-sub">Responden: {{ $selectedUser->nama }} · {{ $selectedUser->email }}</div>
      <div class="hw-sub">ID sesi: {{ $sesi->id }}</div>
      <div class="hw-sub">t0: {{ $sesi->t0?->translatedFormat('d M Y, H:i') ?? 'Belum ditekan di jam' }}</div>
    </div>
  </div>

  <div class="hw-card hw-card-pad">
    <div class="hw-title mb-3">Empat Titik Pengukuran</div>
    <div class="table-responsive">
      <table class="table hw-table">
        <thead><tr><th>Titik</th><th>Status</th><th>Detik dari t0</th><th>Gula Darah</th><th>Detak Jantung</th><th>Tensi</th><th>SpO2</th></tr></thead>
        <tbody>
          @foreach ($sesi->sampel->sortBy('index') as $s)
            <tr>
              <td class="fw-bold" style="font-size:.84rem">{{ $labelTitik[$s->index] ?? "Titik {$s->index}" }}</td>
              <td>
                <span class="hw-pill {{ $s->status === 'terisi' ? 'hw-pill-ok' : ($s->status === 'terlewat' ? 'hw-pill-bad' : 'hw-pill-info') }}">
                  {{ ucfirst($s->status) }}
                </span>
              </td>
              <td style="font-size:.84rem;color:var(--hw-ink-2)">{{ $s->detik_relatif_t0 }}s</td>
              <td class="fw-bold" style="font-size:.84rem">{{ $s->gula_darah ?? '—' }} <span class="fw-normal" style="color:var(--hw-muted-2);font-size:.75rem">mg/dL</span></td>
              <td class="fw-bold" style="font-size:.84rem">{{ $s->detak_jantung ?? '—' }} <span class="fw-normal" style="color:var(--hw-muted-2);font-size:.75rem">bpm</span></td>
              <td class="fw-bold" style="font-size:.84rem">{{ $s->sistolik && $s->diastolik ? "{$s->sistolik}/{$s->diastolik}" : '—' }}</td>
              <td class="fw-bold" style="font-size:.84rem">{{ $s->spo2 ?? '—' }}{{ $s->spo2 ? '%' : '' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="hw-card hw-card-pad">
    <div class="hw-title mb-3">Hasil Analisis Nutrisi</div>
    @if ($sesi->hasilDeteksi)
      <div class="d-flex flex-wrap gap-4 mb-3">
        <div>
          <div class="fw-bold" style="font-size:.7rem;color:var(--hw-muted-2);text-transform:uppercase;letter-spacing:.5px">Indeks Glikemik</div>
          <div class="fw-bold mt-1" style="font-size:.9rem">{{ ucfirst($sesi->hasilDeteksi->indeks_glikemik_perkiraan) }}</div>
        </div>
        <div>
          <div class="fw-bold" style="font-size:.7rem;color:var(--hw-muted-2);text-transform:uppercase;letter-spacing:.5px">Keyakinan</div>
          <div class="fw-bold mt-1" style="font-size:.9rem">{{ $sesi->hasilDeteksi->keyakinan !== null ? round($sesi->hasilDeteksi->keyakinan * 100) . '%' : '—' }}</div>
        </div>
        <div>
          <div class="fw-bold" style="font-size:.7rem;color:var(--hw-muted-2);text-transform:uppercase;letter-spacing:.5px">Dikoreksi Pengguna</div>
          <div class="fw-bold mt-1" style="font-size:.9rem">{{ $sesi->hasilDeteksi->dikoreksi_user ? 'Ya' : 'Belum' }}</div>
        </div>
        <div>
          <div class="fw-bold" style="font-size:.7rem;color:var(--hw-muted-2);text-transform:uppercase;letter-spacing:.5px">Total Kalori</div>
          <div class="fw-bold mt-1" style="font-size:.9rem">{{ $sesi->hasilDeteksi->total_kalori ?? '—' }} kcal</div>
        </div>
      </div>
      <div class="row g-3">
        @foreach ($sesi->hasilDeteksi->itemMakanan as $item)
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="border rounded-4 p-3 h-100" style="border-color:#EDF6F1!important">
              <div class="fw-bold" style="font-size:.84rem">{{ $item->nama }}</div>
              <div style="font-size:.78rem;color:var(--hw-muted)" class="mt-2">{{ $item->porsi }} · {{ $item->estimasi_gram }} g</div>
              <div style="font-size:.78rem;color:var(--hw-muted)" class="mt-1">{{ $item->kalori }} kcal · gula {{ $item->gula_total }} g</div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="hw-note">Belum ada hasil analisis nutrisi untuk sesi ini.</div>
    @endif
  </div>

@endsection
