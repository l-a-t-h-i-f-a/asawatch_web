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
  $fotoUrl = $sesi->foto_disk_path ? route('admin.users.session.foto', [$selectedUser, $sesi]) : null;
  // null pada nilai gizi berarti "tidak diketahui" — makanan di luar tabel
  // TKPI, atau zat yang tabelnya memang tidak punya kolomnya (gula). Jangan
  // dicetak sebagai kosong, apalagi sebagai nol.
  $angka = fn ($nilai, $satuan = '') => $nilai === null ? '—' : $nilai . $satuan;
  $tidakLengkap = $sesi->hasilDeteksi?->zat_tidak_lengkap ?? [];
  // Padanan longgar sengaja ditandai berbeda: angkanya sah dipakai, tapi saat
  // analisis nanti harus bisa dipisahkan dari yang cocok persis.
  $labelCocok = [
      'tepat' => ['Cocok persis', 'hw-pill-ok'],
      'alias' => ['Lewat alias', 'hw-pill-ok'],
      'generik' => ['Padanan generik', 'hw-pill-warn'],
      // Dicocokkan otomatis oleh LLM saat permintaan berjalan, belum pernah
      // diperiksa siapa pun. Angkanya sah dipakai, tapi inilah baris yang
      // perlu disisir sebelum datanya dianggap final.
      'alias_llm' => ['Otomatis, belum diperiksa', 'hw-pill-bad'],
  ];
  $labelZat = [
      'kalori' => 'kalori', 'karbohidrat' => 'karbohidrat', 'protein' => 'protein',
      'lemak' => 'lemak', 'gula_total' => 'gula', 'serat' => 'serat',
  ];
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
    <div class="hw-title mb-3">Foto Makanan &amp; Hasil Analisis Nutrisi</div>
    <div class="row g-4">
      <div class="col-12 col-lg-4">
        @if ($fotoUrl)
          {{-- Foto ada di disk privat; tautan ini pun hanya bisa dibuka admin yang sedang login. --}}
          <a href="{{ $fotoUrl }}" target="_blank" rel="noopener" title="Buka foto ukuran penuh">
            <img src="{{ $fotoUrl }}" alt="Foto makanan sesi {{ $sesi->waktu_foto->translatedFormat('d M Y, H:i') }}"
                 class="w-100 rounded-4" style="max-height:320px;object-fit:cover;background:var(--hw-tint)">
          </a>
          <div class="hw-sub mt-2">Foto yang dianalisis · klik untuk ukuran penuh</div>
        @else
          <div class="rounded-4 d-grid text-center p-4 h-100"
               style="background:var(--hw-tint);color:var(--hw-green-600);min-height:180px;align-content:center">
            <i class="bi bi-image" style="font-size:1.6rem"></i>
            <div class="hw-sub mt-2">Sesi ini belum mengunggah foto makanan.</div>
          </div>
        @endif
      </div>
      <div class="col-12 col-lg-8">
    @if ($sesi->hasilDeteksi)
      <div class="d-flex flex-wrap gap-4 mb-3">
        <div>
          <div class="fw-bold" style="font-size:.7rem;color:var(--hw-muted-2);text-transform:uppercase;letter-spacing:.5px">Indeks Glikemik</div>
          <div class="fw-bold mt-1" style="font-size:.9rem">{{ $sesi->hasilDeteksi->indeks_glikemik_perkiraan ? ucfirst($sesi->hasilDeteksi->indeks_glikemik_perkiraan) : 'Belum tersedia' }}</div>
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
          <div class="fw-bold mt-1" style="font-size:.9rem">@if (! $sesi->hasilDeteksi->total_kalori)
              {{-- Nol di sini bukan "tanpa kalori": tidak ada satu pun makanan
                   yang namanya ketemu di tabel gizi. --}}
              Belum ada angka
            @else
              {{ in_array('kalori', $tidakLengkap) ? '≥ ' : '' }}{{ $sesi->hasilDeteksi->total_kalori }} kcal
            @endif</div>
        </div>
      </div>
      @if ($tidakLengkap)
        {{-- Totalnya jumlah parsial: ada makanan yang tidak menyumbang angka
             untuk zat-zat ini, jadi angkanya "sekurang-kurangnya sekian". --}}
        <div class="hw-note mb-3">
          Total belum lengkap untuk
          <strong>{{ collect($tidakLengkap)->map(fn ($z) => $labelZat[$z] ?? $z)->join(', ', ' dan ') }}</strong> —
          ada makanan pada sesi ini yang tidak menyumbang angka untuk zat tersebut.
        </div>
      @endif
      <div class="row g-3">
        @foreach ($sesi->hasilDeteksi->itemMakanan as $item)
          <div class="col-12 col-sm-6">
            <div class="border rounded-4 p-3 h-100" style="border-color:#EDF6F1!important">
              <div class="fw-bold" style="font-size:.84rem">{{ $item->nama }}</div>
              <div style="font-size:.78rem;color:var(--hw-muted)" class="mt-2">{{ $item->porsi }} · {{ $angka($item->estimasi_gram, ' g') }}</div>
              @if ($item->sumber_gizi)
                <div class="mt-2 d-flex align-items-center gap-2 flex-wrap" style="font-size:.72rem;color:var(--hw-muted-2)">
                  <span>Gizi dari <strong>{{ $item->sumber_gizi }}</strong></span>
                  @if ($label = $labelCocok[$item->cocok] ?? null)
                    <span class="hw-pill {{ $label[1] }}" style="font-size:.62rem">{{ $label[0] }}</span>
                  @endif
                </div>
              @endif
              @if ($item->kalori === null)
                {{-- Nama makanan tidak ketemu di TKPI, jadi tidak ada angkanya sama sekali. --}}
                <div style="font-size:.78rem;color:var(--hw-muted-2)" class="mt-1">Gizi tidak ada di tabel TKPI</div>
              @else
                <div style="font-size:.78rem;color:var(--hw-muted)" class="mt-1">{{ $angka($item->kalori, ' kcal') }} · gula {{ $angka($item->gula_total, ' g') }}</div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="hw-note">Belum ada hasil analisis nutrisi untuk sesi ini.</div>
    @endif
      </div>
    </div>
  </div>

@endsection
