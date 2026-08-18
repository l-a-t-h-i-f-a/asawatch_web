<div class="hw-brand">
  <div class="hw-brand-mark"><i class="bi bi-heart-pulse-fill"></i></div>
  <div>
    <div class="hw-brand-name">Asa<span>Watch</span></div>
    <div class="hw-brand-sub">Portal Web Pribadi</div>
  </div>
</div>

<div>
  <div class="hw-navlabel">MENU UTAMA</div>
  <nav class="hw-nav nav flex-column">
    <a class="nav-link @if(($active ?? null) === 'dashboard') active @endif" href="{{ route('admin.dashboard') }}">
      <i class="bi bi-grid-1x2"></i><span class="flex-grow-1">Dashboard</span>
    </a>
    <a class="nav-link @if(($active ?? null) === 'responden') active @endif" href="{{ route('admin.responden.index') }}">
      <i class="bi bi-clock-history"></i><span class="flex-grow-1">Riwayat Sesi</span>
      <span class="badge rounded-pill">{{ $totalSesi ?? 0 }}</span>
    </a>
    <a class="nav-link" href="{{ route('admin.responden.latest-session') }}">
      <i class="bi bi-clock-history"></i><span class="flex-grow-1">Sesi Terbaru</span>
    </a>
     <a class="nav-link @if(($active ?? null) === 'detail') active @endif" href="{{ route('admin.responden.detail') }}">
      <i class="bi bi-person-vcard"></i><span class="flex-grow-1">Detail Responden</span>
    </a>
    <a class="nav-link @if(($active ?? null) === 'analitik') active @endif" href="{{ route('admin.analitik') }}">
      <i class="bi bi-graph-up-arrow"></i><span class="flex-grow-1">Analitik Saya</span>
    </a>
    <a class="nav-link @if(($active ?? null) === 'ekspor') active @endif" href="{{ route('admin.ekspor.index') }}">
      <i class="bi bi-cloud-arrow-down"></i><span class="flex-grow-1">Ekspor Data Saya</span>
    </a>
  </nav>
</div>

<div class="mt-auto p-3" style="background:#EFFAF4;border:1px solid #D6EFE2;border-radius:1rem">
  <div class="d-flex align-items-center gap-2 mb-2">
    <i class="bi bi-shield-check" style="color:var(--hw-green-400)"></i>
    <div class="fw-bold" style="font-size:.82rem">Data Terlindungi</div>
  </div>
  <div style="font-size:.75rem;line-height:1.5;color:#5D7A6D">Halaman ini hanya menampilkan data milikmu sendiri — tidak ada akses ke akun pengguna lain.</div>
</div>

<div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:var(--hw-soft)">
  @php $user = auth()->user(); @endphp
  <div class="hw-avatar">{{ $user ? \Illuminate\Support\Str::of($user->nama)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') : '?' }}</div>
  <div class="flex-grow-1 min-w-0">
    <div class="fw-bold text-truncate" style="font-size:.84rem">{{ $user->nama ?? 'Tamu' }}</div>
    <div style="font-size:.72rem;color:var(--hw-muted)" class="text-truncate">{{ $user->email ?? '' }}</div>
  </div>
  <form method="POST" action="{{ route('admin.logout') }}">
    @csrf
    <button type="submit" title="Keluar" class="btn p-0 border-0 bg-transparent" style="color:var(--hw-muted-2)"><i class="bi bi-box-arrow-right"></i></button>
  </form>
</div>
