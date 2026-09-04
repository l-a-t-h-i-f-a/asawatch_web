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
        <div class="hw-title">Rata-rata Asupan per Sesi Makan</div>
        <div class="hw-sub mb-3">Dari {{ number_format($sesiGiziDihitung, 0, ',', '.') }} sesi yang hasil analisis fotonya sudah punya angka gizi</div>
        @if ($sesiGiziDihitung === 0)
          <div class="hw-note">Belum ada sesi dengan hasil analisis nutrisi untuk lingkup ini.</div>
        @else
          @php
            $zatGizi = [
                ['kunci' => 'kalori', 'judul' => 'Kalori', 'satuan' => 'kcal', 'desimal' => 0],
                ['kunci' => 'karbohidrat', 'judul' => 'Karbohidrat', 'satuan' => 'g', 'desimal' => 1],
                ['kunci' => 'protein', 'judul' => 'Protein', 'satuan' => 'g', 'desimal' => 1],
                ['kunci' => 'lemak', 'judul' => 'Lemak', 'satuan' => 'g', 'desimal' => 1],
                ['kunci' => 'gula_total', 'judul' => 'Gula', 'satuan' => 'g', 'desimal' => 1],
                ['kunci' => 'serat', 'judul' => 'Serat', 'satuan' => 'g', 'desimal' => 1],
            ];
            $labelZat = [
                'kalori' => 'kalori', 'karbohidrat' => 'karbohidrat', 'protein' => 'protein',
                'lemak' => 'lemak', 'gula_total' => 'gula', 'serat' => 'serat',
            ];
          @endphp
          <div class="row g-3">
            @foreach ($zatGizi as $zat)
              <div class="col-6 col-sm-4">
                <div class="hw-stat-label">{{ $zat['judul'] }}</div>
                <div class="d-flex align-items-baseline gap-1 mt-1">
                  <div style="font-size:1.25rem;font-weight:800;letter-spacing:-.5px">{{ number_format($rataGizi->{$zat['kunci']} ?? 0, $zat['desimal'], ',', '.') }}</div>
                  <div class="fw-semibold" style="font-size:.74rem;color:var(--hw-muted-2)">{{ $zat['satuan'] }}</div>
                </div>
              </div>
            @endforeach
          </div>
          @if ($sesiGiziParsial > 0)
            {{-- Totalnya jumlah parsial pada sebagian sesi, jadi rata-ratanya
                 lebih rendah dari asupan sebenarnya. --}}
            <div class="hw-note mt-3">
              {{ number_format($sesiGiziParsial, 0, ',', '.') }} dari {{ number_format($sesiGiziDihitung, 0, ',', '.') }} sesi totalnya belum lengkap untuk
              <strong>{{ $zatSeringParsial->map(fn ($z) => $labelZat[$z] ?? $z)->join(', ', ' dan ') }}</strong> —
              rata-rata zat tersebut cenderung lebih rendah dari kenyataan.
            </div>
          @endif
        @endif
      </div>
    </div>
  </div>

@endsection
