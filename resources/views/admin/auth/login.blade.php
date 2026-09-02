<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk · AsaWatch</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('admin/assets/css/asawatch.css') }}">
<link rel="icon" type="image/png" href="{{ asset('admin/assets/logo-mark.png') }}">
</head>
<body>
<div class="hw-login">
  <div class="hw-login-aside">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="hw-brand position-relative p-0">
      <div class="hw-brand-mark is-logo" style="box-shadow:none"><img src="{{ asset('admin/assets/logo-mark.png') }}" alt="Logo AsaWatch"></div>
      <div>
        <div class="hw-brand-name text-white">Asa<span style="color:var(--hw-mint)">Watch</span></div>
        <div class="hw-brand-sub" style="color:rgba(234,247,240,.7)">Panel Administrator</div>
      </div>
    </div>

    <div class="position-relative d-flex flex-column gap-3" style="max-width:440px">
      <div class="hw-login-head">Data sesi makan seluruh responden, terkumpul rapi di satu tempat.</div>
      <div style="font-size:.95rem;line-height:1.65;color:rgba(234,247,240,.82)">Pantau responden, telusuri tiap sesi pengukuran, dan ekspor datanya untuk dianalisis. Khusus akun administrator.</div>
      <div class="d-flex flex-column gap-2 mt-2">
        <div class="d-flex align-items-center gap-3" style="font-size:.88rem;color:rgba(234,247,240,.9)"><span class="hw-icon-box" style="width:26px;height:26px;flex:0 0 26px;border-radius:9px;background:rgba(126,227,176,.2);color:var(--hw-mint);font-size:.8rem"><i class="bi bi-activity"></i></span>4 titik pengukuran per sesi makan, langsung dari perangkat responden</div>
        <div class="d-flex align-items-center gap-3" style="font-size:.88rem;color:rgba(234,247,240,.9)"><span class="hw-icon-box" style="width:26px;height:26px;flex:0 0 26px;border-radius:9px;background:rgba(126,227,176,.2);color:var(--hw-mint);font-size:.8rem"><i class="bi bi-shield-lock"></i></span>Akses terbatas untuk administrator</div>
        <div class="d-flex align-items-center gap-3" style="font-size:.88rem;color:rgba(234,247,240,.9)"><span class="hw-icon-box" style="width:26px;height:26px;flex:0 0 26px;border-radius:9px;background:rgba(126,227,176,.2);color:var(--hw-mint);font-size:.8rem"><i class="bi bi-cloud-arrow-down"></i></span>Ekspor data seluruh responden kapan saja</div>
      </div>
    </div>
  </div>

  <div class="hw-login-form">
    <form class="w-100 d-flex flex-column gap-4" style="max-width:392px" method="POST" action="{{ route('admin.login.submit') }}">
      @csrf
      <div>
        <div style="font-size:1.68rem;font-weight:800;letter-spacing:-.7px">Masuk ke Portal</div>
        <div class="hw-sub" style="font-size:.88rem;line-height:1.55">Masuk dengan akun administrator AsaWatch.</div>
      </div>

      @if ($errors->any())
        <div class="alert alert-danger" style="font-size:.84rem">{{ $errors->first() }}</div>
      @endif

      <div class="d-flex flex-column gap-3">
        <div>
          <label class="fw-bold mb-2" style="font-size:.78rem;color:var(--hw-ink-2)" for="email">Email</label>
          <div class="position-relative">
            <i class="bi bi-envelope position-absolute" style="left:14px;top:13px;color:var(--hw-muted-2)"></i>
            <input class="form-control bg-white ps-5 py-3" id="email" name="email" type="email" value="{{ old('email') }}" required>
          </div>
        </div>

        <div>
          <label class="fw-bold mb-2" style="font-size:.78rem;color:var(--hw-ink-2)" for="pw">Kata sandi</label>
          <div class="position-relative">
            <i class="bi bi-lock position-absolute" style="left:14px;top:13px;color:var(--hw-muted-2)"></i>
            <input class="form-control bg-white ps-5 pe-5 py-3" id="pw" name="password" type="password" required>
            <button class="btn position-absolute end-0 top-0" type="button" data-toggle-password="#pw" style="height:100%;color:var(--hw-muted)"><i class="bi bi-eye"></i></button>
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-between">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label fw-semibold" for="remember" style="font-size:.84rem;color:var(--hw-ink-2)">Ingat perangkat ini</label>
          </div>
          <a href="#lupa" class="fw-semibold" style="font-size:.84rem">Lupa sandi?</a>
        </div>

        <button class="btn btn-hw d-flex align-items-center justify-content-center gap-2" type="submit" style="padding:.9rem;font-size:.95rem;box-shadow:0 8px 18px rgba(18,133,90,.24)">
          Masuk <i class="bi bi-arrow-right"></i>
        </button>
      </div>

      <div class="hw-card p-3 d-flex gap-2">
        <i class="bi bi-info-circle" style="color:var(--hw-green-400)"></i>
        <div style="font-size:.78rem;line-height:1.55;color:#5D7A6D">Belum punya akun? Daftar lewat aplikasi mobile AsaWatch terlebih dahulu, lalu masuk di sini dengan email &amp; kata sandi yang sama.</div>
      </div>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('admin/assets/js/asawatch.js') }}"></script>
</body>
</html>
