<header class="hw-header d-flex align-items-center gap-3 flex-wrap">
  <button class="hw-burger d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#hwNav" aria-controls="hwNav" aria-label="Buka menu">
    <i class="bi bi-list"></i>
  </button>
  <div class="flex-grow-1" style="min-width:220px">
    <div style="font-size:1.32rem;font-weight:800;letter-spacing:-.4px">@yield('page-title', 'AsaWatch Admin')</div>
    <div class="hw-sub">@yield('page-subtitle')</div>
  </div>
  <div class="position-relative" style="flex:0 1 280px;min-width:150px">
    <i class="bi bi-search position-absolute" style="left:14px;top:11px;color:var(--hw-muted-2);font-size:.85rem"></i>
    <input class="form-control ps-5" placeholder="Cari ID sesi">
  </div>
  <button class="btn btn-hw-outline d-flex align-items-center gap-2">
    <i class="bi bi-calendar3" style="color:var(--hw-green-400)"></i> {{ now()->subDays(26)->translatedFormat('j M') }} – {{ now()->translatedFormat('j M Y') }}
  </button>
  <button class="btn btn-hw-outline position-relative" style="width:42px;height:42px;padding:0">
    <i class="bi bi-bell"></i>
    <span class="position-absolute rounded-circle" style="top:8px;right:9px;width:8px;height:8px;background:var(--hw-red);border:2px solid #fff"></span>
  </button>
</header>
