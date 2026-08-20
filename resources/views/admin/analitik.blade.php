@extends('admin.layout')

@section('title', 'Analitik · AsaWatch')
@section('page-title', 'Analitik')
@section('page-subtitle', 'Ringkasan deskriptif dari data pengukuran — bukan kesimpulan medis')

@section('content')

  @include('admin.partials.pilih-responden', ['rute' => 'admin.analitik'])

  <div class="row g-3">
    <div class="col-12 col-lg-3">
      <div class="hw-card hw-card-pad h-100">
        <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">Total titik data terisi</div>
        <div class="d-flex align-items-baseline gap-2 my-2">
          <div style="font-size:1.7rem;font-weight:800;letter-spacing:-1px">{{ number_format($totalTitikData, 0, ',', '.') }}</div>
          <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted-2)">titik</div>
        </div>
        <div class="hw-sub">dari {{ number_format($totalSesi, 0, ',', '.') }} sesi</div>
      </div>
    </div>
    <div class="col-12 col-lg-3">
      <div class="hw-card hw-card-pad h-100">
        <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">Rata-rata gula darah</div>
        <div class="d-flex align-items-baseline gap-2 my-2">
          <div style="font-size:1.7rem;font-weight:800;letter-spacing:-1px">{{ $rataRataGula ?: '—' }}</div>
          <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted-2)">mg/dL</div>
        </div>
        <div class="hw-sub">{{ $titikGulaTinggi }} titik ≥ 180 mg/dL</div>
      </div>
    </div>
    <div class="col-12 col-lg-3">
      <div class="hw-card hw-card-pad h-100">
        <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">Rata-rata tensi</div>
        <div class="d-flex align-items-baseline gap-2 my-2">
          <div style="font-size:1.7rem;font-weight:800;letter-spacing:-1px">{{ $rataRataSistolik ?: '—' }}/{{ $rataRataDiastolik ?: '—' }}</div>
          <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted-2)">mmHg</div>
        </div>
        <div class="hw-sub">{{ $titikHipertensi }} titik sistolik ≥ 140</div>
      </div>
    </div>
    <div class="col-12 col-lg-3">
      <div class="hw-card hw-card-pad h-100">
        <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted)">Rata-rata detak jantung</div>
        <div class="d-flex align-items-baseline gap-2 my-2">
          <div style="font-size:1.7rem;font-weight:800;letter-spacing:-1px">{{ $rataRataDetak ?: '—' }}</div>
          <div class="fw-semibold" style="font-size:.82rem;color:var(--hw-muted-2)">bpm</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-xl-6">
      <div class="hw-card hw-card-pad h-100">
        <div class="hw-title">Rata-rata Gula Darah per Titik Sesi</div>
        <div class="hw-sub mb-4">Baseline · Selesai makan (t0) · +1 jam · +2 jam — skala bar 0–{{ $skalaGulaMaks }} mg/dL</div>
        @php $labelTitik = ['Baseline', 'Selesai Makan (t0)', '+1 Jam', '+2 Jam']; @endphp
        @foreach ($kurvaPerIndex as $titik)
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-2">
              <span class="fw-bold" style="font-size:.84rem">{{ $labelTitik[$titik['index']] }}</span>
              <span class="fw-semibold" style="font-size:.84rem;color:var(--hw-muted)">n = {{ $titik['n'] }}</span>
            </div>
            <div class="hw-progress" style="height:8px"><span style="width:{{ $titik['persen_skala'] }}%;background:var(--hw-blue)"></span></div>
            <div class="fw-semibold mt-2" style="font-size:.72rem;color:var(--hw-muted)">{{ $titik['rata_gula'] ?: '—' }} mg/dL</div>
          </div>
        @endforeach
        @if ($totalTitikData === 0)
          <div class="hw-note">Belum ada titik pengukuran terisi untuk lingkup ini.</div>
        @endif
      </div>
    </div>
    <div class="col-12 col-xl-6">
      <div class="hw-card hw-card-pad h-100">
        <div class="hw-title">Makanan Tinggi Gula Teratas</div>
        <div class="hw-sub mb-3">Dari hasil analisis foto, gula ≥ 15 g per porsi</div>
        <div class="table-responsive">
          <table class="table hw-table">
            <thead><tr><th>Makanan</th><th>Porsi</th>@if($lingkup->semua())<th>Responden</th>@endif<th>Gula (g)</th></tr></thead>
            <tbody>
              @forelse ($makananTinggiGula as $item)
                <tr>
                  <td style="font-size:.84rem">{{ $item->nama }}</td>
                  <td style="font-size:.84rem;color:var(--hw-ink-2)">{{ $item->porsi ?? '—' }}</td>
                  @if($lingkup->semua())
                    <td style="font-size:.84rem;color:var(--hw-ink-2)">{{ $item->sesi?->user?->nama ?? '—' }}</td>
                  @endif
                  <td class="fw-bold text-bad" style="font-size:.84rem">{{ $item->gula_total }}</td>
                </tr>
              @empty
                <tr><td colspan="{{ $lingkup->semua() ? 4 : 3 }}" class="text-center text-muted py-4">Belum ada catatan makanan tinggi gula.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

@endsection
