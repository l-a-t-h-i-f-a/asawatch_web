<header class="hw-header d-flex align-items-center gap-3 flex-wrap">
  <button class="hw-burger d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#hwNav" aria-controls="hwNav" aria-label="Buka menu">
    <i class="bi bi-list"></i>
  </button>
  <div class="flex-grow-1" style="min-width:220px">
    <div style="font-size:1.32rem;font-weight:800;letter-spacing:-.4px">@yield('page-title', 'AsaWatch Admin')</div>
    <div class="hw-sub">@yield('page-subtitle')</div>
  </div>
  <form class="position-relative" style="flex:0 1 320px;min-width:180px" method="GET" action="{{ route('admin.cari') }}" role="search">
    <i class="bi bi-search position-absolute" style="left:14px;top:11px;color:var(--hw-muted-2);font-size:.85rem"></i>
    <input class="form-control ps-5" name="q" value="{{ request('q') }}" placeholder="Cari responden atau ID sesi" aria-label="Cari responden atau ID sesi">
  </form>
</header>
