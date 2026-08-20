@extends('admin.layout')

@section('title', 'Dashboard · AsaWatch')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan seluruh responden AsaWatch · diperbarui ' . now()->format('H:i'))

@section('content')

  <div class="row g-3">
    <div class="col-12 col-sm-6 col-xxl-3">
      <div class="hw-card hw-card-pad h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="hw-icon-box" style="background:var(--hw-tint);color:var(--hw-green-600)"><i class="bi bi-people-fill"></i></div>
        </div>
        <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">Total Responden</div>
        <div class="d-flex align-items-baseline gap-1 mt-1">
          <div class="hw-kpi-value">{{ $totalResponden }}</div>
          <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">orang</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl-3">
      <div class="hw-card hw-card-pad h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="hw-icon-box" style="background:#EAF7F0;color:var(--hw-green-600)"><i class="bi bi-clock-history"></i></div>
        </div>
        <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">Total Sesi</div>
        <div class="d-flex align-items-baseline gap-1 mt-1">
          <div class="hw-kpi-value">{{ $totalSesi }}</div>
          <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">sesi</div>
        </div>
        <div class="hw-sub mt-1">{{ $sesiSelesai }} sesi selesai</div>
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
        <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">Perangkat Terdaftar</div>
        <div class="d-flex align-items-baseline gap-1 mt-1">
          <div class="hw-kpi-value">{{ $perangkatTerdaftar }}</div>
          <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">watch</div>
        </div>
      </div>
    </div>
  </div>

  <div class="hw-card hw-card-pad">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
      <div>
        <div class="hw-title">Responden Paling Aktif</div>
        <div class="hw-sub">Diurutkan dari jumlah sesi makan yang tercatat</div>
      </div>
      <a class="btn btn-hw-soft" href="{{ route('admin.users.index') }}">Lihat semua responden</a>
    </div>
    <div class="table-responsive">
      <table class="table hw-table align-middle">
        <thead><tr><th>Responden</th><th>Total Sesi</th><th>Sesi Selesai</th><th></th></tr></thead>
        <tbody>
          @forelse ($respondenTeraktif as $r)
            <tr>
              <td>
                <div class="fw-bold" style="font-size:.86rem">{{ $r->nama }}</div>
                <div style="font-size:.75rem;color:var(--hw-muted)">{{ $r->email }}</div>
              </td>
              <td class="fw-bold" style="font-size:.84rem">{{ $r->sesi_count }}</td>
              <td class="fw-bold" style="font-size:.84rem">{{ $r->sesi_selesai_count }}</td>
              <td class="text-end"><a class="btn btn-hw-outline btn-sm" href="{{ route('admin.users.show', $r) }}">Lihat Detail</a></td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada responden terdaftar.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="hw-card hw-card-pad">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
      <div>
        <div class="hw-title">Pengukuran di Luar Rentang Normal</div>
        <div class="hw-sub">Gula darah ≥ 180 mg/dL, sistolik ≥ 140 mmHg, atau detak jantung ≥ 100 bpm</div>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table hw-table">
        <thead><tr><th>Responden</th><th>Sesi</th><th>Titik</th><th>Gula Darah</th><th>Tensi</th><th>Detak</th><th></th></tr></thead>
        <tbody>
          @forelse ($sampelPerluPerhatian as $s)
            <tr>
              <td class="fw-bold" style="font-size:.84rem">{{ $s->sesi->user->nama ?? '—' }}</td>
              <td style="font-size:.84rem;color:var(--hw-ink-2)">{{ $s->sesi->waktu_foto->translatedFormat('d M Y, H:i') }}</td>
              <td style="font-size:.84rem;color:var(--hw-ink-2)">Titik {{ $s->index + 1 }}</td>
              <td class="fw-bold" style="font-size:.84rem">{{ $s->gula_darah ?? '—' }} <span class="fw-normal" style="color:var(--hw-muted-2);font-size:.75rem">mg/dL</span></td>
              <td class="fw-bold" style="font-size:.84rem">{{ $s->sistolik && $s->diastolik ? "{$s->sistolik}/{$s->diastolik}" : '—' }}</td>
              <td class="fw-bold" style="font-size:.84rem">{{ $s->detak_jantung ?? '—' }} <span class="fw-normal" style="color:var(--hw-muted-2);font-size:.75rem">bpm</span></td>
              <td class="text-end">
                @if ($s->sesi->user)
                  <a class="btn btn-hw-outline" href="{{ route('admin.users.session.show', [$s->sesi->user, $s->sesi]) }}">Detail</a>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada pengukuran di luar rentang normal.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

@endsection
