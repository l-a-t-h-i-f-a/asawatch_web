@extends('admin.layout')

@section('title', 'Ekspor Data · AsaWatch')
@section('page-title', 'Ekspor Data')
@section('page-subtitle', 'Unduh data responden untuk dianalisis di luar aplikasi')

@php
  $preset = fn (?string $dari, ?string $sampai) => route('admin.ekspor.index', array_merge(
    $saringan->parameterQuery(), ['format' => $format->value, 'dari' => $dari, 'sampai' => $sampai]
  ));

  $hariIni = \App\Support\Waktu::tanggal(now(), 'Y-m-d');
  $angka = fn (int $n) => number_format($n, 0, ',', '.');

  // Ringkas saringan yang sedang berlaku jadi label pendek, supaya isi berkas
  // bisa dibaca sekilas tanpa menelusuri kembali isian di sebelah kiri.
  $chip = array_filter([
    $lingkup->semua() ? 'Semua responden' : $lingkup->user->nama,
    $saringan->labelPeriode(),
    $saringan->sertakanSesiUji ? null : 'Tanpa sesi uji',
  ]);
@endphp

@section('content')

  {{--
    Satu form untuk saringan sekaligus unduhan. Bentuk berkas ikut jadi
    parameter saringan supaya panel kanan bisa menyesuaikan diri di sisi
    server — tombol unduhnya cukup satu dan tetap bekerja tanpa JavaScript.
  --}}
  <form method="GET" action="{{ route('admin.ekspor.index') }}" id="form-ekspor">
    <div class="row g-3 align-items-start">

      {{-- ------------------------------------------------------- kiri --}}
      <div class="col-12 col-xl-8 d-flex flex-column gap-3">

        <div class="hw-card hw-card-pad d-flex flex-column gap-3">
          <div class="hw-step">
            <span class="hw-step-no">1</span>
            <div class="flex-grow-1">
              <div class="hw-title">Pilih data</div>
              <div class="hw-sub">Siapa, dan rentang waktu yang mana.</div>
            </div>
            @if ($saringan->aktif())
              <a href="{{ route('admin.ekspor.index', ['format' => $format->value]) }}" class="btn btn-hw-outline btn-sm">
                <i class="bi bi-arrow-counterclockwise"></i> Atur ulang
              </a>
            @endif
          </div>

          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label fw-bold" for="responden" style="font-size:.8rem">Responden</label>
              <select class="form-select" id="responden" name="responden" data-muat-ulang>
                <option value="">Semua responden ({{ $lingkup->daftar->count() }})</option>
                @foreach ($lingkup->daftar as $r)
                  <option value="{{ $r->id }}" @selected($lingkup->user?->id === $r->id)>{{ $r->nama }} — {{ $r->email }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-6 col-md-3">
              <label class="form-label fw-bold" for="dari" style="font-size:.8rem">Dari tanggal</label>
              <input type="date" class="form-control" id="dari" name="dari" max="{{ $hariIni }}"
                     value="{{ $saringan->dariInput }}" data-muat-ulang>
            </div>

            <div class="col-6 col-md-3">
              <label class="form-label fw-bold" for="sampai" style="font-size:.8rem">Sampai tanggal</label>
              <input type="date" class="form-control" id="sampai" name="sampai" max="{{ $hariIni }}"
                     value="{{ $saringan->sampaiInput }}" data-muat-ulang>
            </div>
          </div>

          <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="hw-sub me-1">Pintasan periode</span>
            <a class="btn btn-hw-outline btn-sm" href="{{ $preset(\App\Support\Waktu::tanggal(now()->subDays(29), 'Y-m-d'), $hariIni) }}">30 hari terakhir</a>
            <a class="btn btn-hw-outline btn-sm" href="{{ $preset(\App\Support\Waktu::tanggal(now()->subDays(89), 'Y-m-d'), $hariIni) }}">90 hari terakhir</a>
            <a class="btn btn-hw-outline btn-sm @if ($saringan->dariInput === null && $saringan->sampaiInput === null) active @endif" href="{{ $preset(null, null) }}">Seluruh periode</a>
            <noscript><button type="submit" class="btn btn-hw btn-sm">Terapkan</button></noscript>
          </div>

          <div class="row g-3">
            {{-- Hidden input mendahului sakelar supaya centang yang dilepas tetap terkirim. --}}
            <input type="hidden" name="sesi_uji" value="0">
            <div class="col-12 col-md-6">
              <div class="form-check form-switch mb-1">
                <input class="form-check-input" type="checkbox" role="switch" id="sesi_uji" name="sesi_uji" value="1"
                       @checked($saringan->sertakanSesiUji) data-muat-ulang>
                <label class="form-check-label fw-bold" for="sesi_uji" style="font-size:.84rem">Sertakan sesi uji</label>
              </div>
              <div class="hw-sub" style="margin-top:0">Sesi pengujian dengan jadwal dimampatkan atau perangkat palsu.</div>
            </div>

          </div>
        </div>

        <div class="hw-card hw-card-pad d-flex flex-column gap-3">
          <div class="hw-step">
            <span class="hw-step-no">2</span>
            <div>
              <div class="hw-title">Pilih bentuk berkas</div>
              <div class="hw-sub">Isinya sama — yang berbeda cara membacanya.</div>
            </div>
          </div>

          <div class="row g-2">
            @foreach ($semuaFormat as $f)
              <div class="col-12 col-md-4">
                <input type="radio" class="btn-check" name="format" id="format-{{ $f->value }}"
                       value="{{ $f->value }}" @checked($format === $f) data-muat-ulang>
                <label class="hw-format" for="format-{{ $f->value }}">
                  <i class="bi {{ $f->ikon() }}"></i>
                  <div class="fw-bold mt-2" style="font-size:.92rem">{{ $f->judul() }}</div>
                  <div class="hw-sub">{{ $f->untukSiapa() }}</div>
                </label>
              </div>
            @endforeach
          </div>

          <div class="hw-note">{{ $format->penjelasan() }}</div>

          @if ($format === \App\Services\Ekspor\FormatEkspor::CSV)
            <div>
              <label class="form-label fw-bold" for="pemisah" style="font-size:.8rem">Pemisah kolom</label>
              <select class="form-select" id="pemisah" name="pemisah" style="max-width:26rem">
                <option value="koma" @selected(request('pemisah') !== 'titik_koma')>Koma — untuk SPSS, R, pandas</option>
                <option value="titik_koma" @selected(request('pemisah') === 'titik_koma')>Titik koma — agar rapi di Excel berlokal Indonesia</option>
              </select>
              <div class="hw-sub">Excel berbahasa Indonesia membaca koma sebagai desimal, sehingga berkas berpemisah koma menumpuk di satu kolom.</div>
            </div>
          @endif

          <div class="hw-sub">
            Satu <span class="fw-bold" style="color:var(--hw-ink-2)">sesi</span> = satu kali makan, berisi 4 titik pengukuran:
            −30 menit, saat makan, +1 jam, +2 jam.
          </div>
        </div>
      </div>

      {{-- ------------------------------------------------------ kanan --}}
      <div class="col-12 col-xl-4">
        <div class="hw-card hw-card-pad hw-sticky d-flex flex-column gap-3">
          <div>
            <div class="hw-title">Isi Berkas</div>
            <div class="hw-sub">Yang akan turun kalau tombol di bawah ditekan.</div>
          </div>

          <div class="d-flex flex-wrap gap-1">
            @foreach ($chip as $teks)
              <span class="hw-chip">{{ $teks }}</span>
            @endforeach
          </div>

          @if ($ringkasan->kosong())
            <div class="hw-note d-flex gap-2">
              <i class="bi bi-exclamation-triangle" style="color:var(--hw-orange)"></i>
              <div>Tidak ada sesi yang cocok dengan saringan ini. Longgarkan rentang tanggal atau sertakan kembali sesi uji.</div>
            </div>
          @else
            <div class="hw-berkas">
              <i class="bi {{ $format->ikon() }}"></i>
              <span>{{ $format->namaBerkas($saringan) }}</span>
            </div>

            <div class="d-flex" style="border-top:1px solid #F3F9F6;border-bottom:1px solid #F3F9F6;padding:.9rem 0">
              <div class="hw-stat"><b>{{ $angka($ringkasan->responden) }}</b><span>Responden</span></div>
              <div class="hw-stat"><b>{{ $angka($ringkasan->sesi) }}</b><span>Sesi</span></div>
              <div class="hw-stat"><b>{{ $angka($ringkasan->sampel) }}</b><span>Titik ukur</span></div>
            </div>

            @if ($format->berlembar())
              <div>
                <div class="fw-bold mb-1" style="font-size:.8rem">{{ count($ringkasan->perLembar()) }} lembar di dalam berkas</div>
                @foreach ($ringkasan->perLembar() as $nama => $jumlah)
                  <div class="hw-lembar">
                    <span style="color:var(--hw-ink-2)" title="{{ $penjelasanLembar[$nama] ?? '' }}">{{ $nama }}</span>
                    <span class="fw-bold text-nowrap">{{ $angka($jumlah) }}</span>
                  </div>
                @endforeach
              </div>
            @else
              <div class="hw-sub" style="margin-top:0">
                @if ($format === \App\Services\Ekspor\FormatEkspor::CSV)
                  Satu tabel berisi {{ $angka($ringkasan->sampel) }} baris — satu baris per titik pengukuran.
                @else
                  Satu berkas berisi {{ $angka($ringkasan->responden) }} responden beserta seluruh sesi, pengukuran, gizi, kalibrasi, dan perangkatnya.
                @endif
              </div>
            @endif

            <div class="hw-sub" style="margin-top:0">
              {{ $angka($ringkasan->sampelTerisi) }} dari {{ $angka($ringkasan->sampel) }} titik benar-benar terukur
              @if ($ringkasan->sesiUji > 0) · {{ $angka($ringkasan->sesiUji) }} sesi uji @endif
            </div>
          @endif

          <button type="submit" formaction="{{ route('admin.ekspor.unduh') }}"
                  class="btn btn-hw w-100 d-flex align-items-center justify-content-center gap-2"
                  style="padding:.8rem" data-unduh @disabled($ringkasan->kosong())>
            <i class="bi bi-download"></i> Unduh {{ $format->judul() }}
          </button>

          <div class="hw-sub" style="margin-top:0">
            Sel kosong berarti nilainya tidak ada — bukan nol. Waktu dalam {{ \App\Support\Waktu::LABEL_ZONA }}.
          </div>
        </div>
      </div>

    </div>
  </form>

@endsection

@push('scripts')
<script>
  (function () {
    const form = document.getElementById('form-ekspor');
    if (! form) return;

    // Saringan diterapkan begitu diubah, supaya panel kanan tidak pernah
    // tertinggal dari isian di kiri. Tanpa JavaScript, tombol "Terapkan" di
    // dalam <noscript> mengambil alih peran ini.
    form.querySelectorAll('[data-muat-ulang]').forEach(function (kolom) {
      kolom.addEventListener('change', function () { form.submit(); });
    });

    // Unduhan tidak memuat ulang halaman, jadi tombol dikembalikan sendiri
    // setelah beberapa detik — cukup untuk menandai "sedang disiapkan" pada
    // ekspor besar tanpa mengunci tombol selamanya.
    form.querySelectorAll('[data-unduh]').forEach(function (tombol) {
      tombol.addEventListener('click', function () {
        const isi = tombol.innerHTML;
        setTimeout(function () {
          tombol.disabled = true;
          tombol.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyiapkan…';
        }, 50);
        setTimeout(function () {
          tombol.disabled = false;
          tombol.innerHTML = isi;
        }, 6000);
      });
    });
  })();
</script>
@endpush
