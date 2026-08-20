{{--
  Pemilih lingkup data untuk Analitik & Ekspor.
  Butuh: $lingkup (App\Support\LingkupResponden) dan $rute (nama route tujuan).
--}}
<form method="GET" action="{{ route($rute) }}" class="hw-card d-flex align-items-center gap-3 flex-wrap" style="padding:1.1rem 1.35rem">
  <div class="d-flex align-items-center gap-2">
    <i class="bi bi-funnel" style="color:var(--hw-green-600)"></i>
    <label class="fw-bold mb-0" for="responden" style="font-size:.82rem">Lingkup data</label>
  </div>
  <select class="form-select" id="responden" name="responden" style="max-width:320px;font-size:.88rem;border-color:#EDF6F1" onchange="this.form.submit()">
    <option value="">Semua responden ({{ $lingkup->daftar->count() }})</option>
    @foreach ($lingkup->daftar as $r)
      <option value="{{ $r->id }}" @selected($lingkup->user?->id === $r->id)>{{ $r->nama }} — {{ $r->email }}</option>
    @endforeach
  </select>
  <noscript><button type="submit" class="btn btn-hw">Terapkan</button></noscript>
  @if (! $lingkup->semua())
    <a href="{{ route($rute) }}" class="btn btn-hw-outline btn-sm">Tampilkan semua</a>
  @endif
  <div class="ms-auto hw-sub">Menampilkan: <span class="fw-bold" style="color:var(--hw-ink-2)">{{ $lingkup->label() }}</span></div>
</form>
