@extends('admin.layout')

@section('title', 'Dashboard · AsaWatch')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan sesi makan dan pengukuranmu · diperbarui ' . now()->format('H:i'))

@section('content')

  <div class="row g-3">
    <div class="col-12 col-sm-6 col-xxl-3">
      <div class="hw-card hw-card-pad h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="hw-icon-box" style="background:var(--hw-tint);color:var(--hw-green-600)"><i class="bi bi-clock-history"></i></div>
        </div>
        <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">Total Sesi</div>
        <div class="d-flex align-items-baseline gap-1 mt-1">
          <div class="hw-kpi-value">{{ $totalSesi }}</div>
          <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">sesi</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl-3">
      <div class="hw-card hw-card-pad h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="hw-icon-box" style="background:#EAF7F0;color:var(--hw-green-600)"><i class="bi bi-check-circle-fill"></i></div>
        </div>
        <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">Sesi Selesai</div>
        <div class="d-flex align-items-baseline gap-1 mt-1">
          <div class="hw-kpi-value">{{ $sesiSelesai }}</div>
          <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">sesi</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl-3">
      <div class="hw-card hw-card-pad h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="hw-icon-box" style="background:#E9F4FB;color:#1B7FA8"><i class="bi bi-droplet-fill"></i></div>
        </div>
        <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">Rata-rata Gula Darah Hari Ini</div>
        <div class="d-flex align-items-baseline gap-1 mt-1">
          <div class="hw-kpi-value">{{ $rataRataGula ?: '—' }}</div>
          <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">mg/dL</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl-3">
      <div class="hw-card hw-card-pad h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="hw-icon-box" style="background:var(--hw-ink);color:var(--hw-mint)"><i class="bi bi-smartwatch"></i></div>
        </div>
        <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">Perangkat Terhubung</div>
        <div class="d-flex align-items-baseline gap-1 mt-1">
          <div class="hw-kpi-value">{{ $perangkatTersambung }}</div>
          <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">watch</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-xl-8">
      <div class="hw-card hw-card-pad h-100">
        <div class="hw-title">Tren Sesi Makan</div>
        <div class="hw-sub">Grafik tren gula darah antar sesi ditampilkan di aplikasi mobile — halaman ini fokus ke ringkasan &amp; ekspor data.</div>
      </div>
    </div>
    <div class="col-12 col-xl-4">
      <div class="hw-card hw-card-pad h-100 d-flex flex-column gap-3">
        <div>
          <div class="hw-title">Kalibrasi Tensi Terakhir</div>
          <div class="hw-sub">Selisih pengukuran jam vs tensimeter referensi</div>
        </div>
        @if ($kalibrasiTerakhir)
          <div>
            <div class="fw-bold" style="font-size:1.1rem">{{ $kalibrasiTerakhir->sistolik_jam }}/{{ $kalibrasiTerakhir->diastolik_jam }} <span class="fw-normal" style="font-size:.8rem;color:var(--hw-muted)">mmHg (jam)</span></div>
            <div class="fw-semibold mt-1" style="font-size:.84rem;color:var(--hw-muted)">Referensi: {{ $kalibrasiTerakhir->sistolik_referensi }}/{{ $kalibrasiTerakhir->diastolik_referensi }} mmHg</div>
            <div class="mt-2" style="font-size:.78rem;color:var(--hw-muted-2)">{{ $kalibrasiTerakhir->waktu->translatedFormat('d M Y, H:i') }}</div>
          </div>
        @else
          <div class="hw-note">Belum ada kalibrasi tersimpan.</div>
        @endif
      </div>
    </div>
  </div>

  <div class="hw-card hw-card-pad">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
      <div>
        <div class="hw-title">Pengukuran di Luar Rentang Normal</div>
        <div class="hw-sub">Gula darah ≥ 180 mg/dL, sistolik ≥ 140 mmHg, atau detak jantung ≥ 100 bpm</div>
      </div>
      <a class="btn btn-hw-soft" href="{{ route('admin.responden.index') }}">Lihat semua sesi</a>
    </div>
    <div class="table-responsive">
      <table class="table hw-table">
        <thead><tr><th>Sesi</th><th>Titik</th><th>Gula Darah</th><th>Tensi</th><th>Detak</th><th></th></tr></thead>
        <tbody>
          @forelse ($sampelPerluPerhatian as $s)
            <tr>
              <td style="font-size:.84rem;color:var(--hw-ink-2)">{{ $s->sesi->waktu_foto->translatedFormat('d M Y, H:i') }}</td>
              <td style="font-size:.84rem;color:var(--hw-ink-2)">Titik {{ $s->index + 1 }}</td>
              <td class="fw-bold" style="font-size:.84rem">{{ $s->gula_darah ?? '—' }} <span class="fw-normal" style="color:var(--hw-muted-2);font-size:.75rem">mg/dL</span></td>
              <td class="fw-bold" style="font-size:.84rem">{{ $s->sistolik && $s->diastolik ? "{$s->sistolik}/{$s->diastolik}" : '—' }}</td>
              <td class="fw-bold" style="font-size:.84rem">{{ $s->detak_jantung ?? '—' }} <span class="fw-normal" style="color:var(--hw-muted-2);font-size:.75rem">bpm</span></td>
              <td class="text-end"><a class="btn btn-hw-outline" href="{{ route('admin.responden.show', $s->sesi) }}">Detail</a></td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada pengukuran di luar rentang normal.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

@endsection
