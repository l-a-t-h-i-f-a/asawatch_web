# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

AsaWatch backend (Laravel 12, PHP 8.2) — a health-watch study platform for elderly respondents. It serves two consumers:

1. **`/api/v1/*`** — a token-authenticated (Sanctum) JSON API for the Flutter mobile app, which is offline-first and syncs sessions.
2. **`/admin/*`** — a session-authenticated Blade UI (Bootstrap 5) for researchers.

Domain language is **Indonesian** (`sesi`, `sampel`, `kalibrasi`, `perangkat`, `hasil_deteksi`, `galat`). Tables, columns, JSON keys, route names, and most class names follow this; the framework-side names stay English. Follow the existing language when adding code — do not "translate" names to English.

Rincian skema database dan alur kerja end-to-end ada di [docs/ARSITEKTUR.md](docs/ARSITEKTUR.md).

Code comments repeatedly cite "bagian N dokumen API" (the API contract document). That document is **not in this repo**; treat those references as pointers to a spec held elsewhere and don't invent its contents.

## Commands

```bash
composer setup                  # install, .env, key:generate, migrate, npm install+build
composer dev                    # serve + queue:listen + vite concurrently
composer test                   # config:clear then artisan test
php artisan test --filter=NamaTest          # single test
php artisan test tests/Feature/ExampleTest.php
vendor/bin/pint                 # format (Laravel Pint)
php artisan queue:work          # analysis + account-deletion jobs (QUEUE_CONNECTION=database)
php artisan migrate:fresh --seed
php artisan db:seed --class=SesiLengkapSeeder     # satu sesi contoh lengkap untuk akun admin
php artisan db:seed --class=PenggunaContohSeeder  # 4 pengguna non-admin + 8 sesi, untuk menu "Daftar Pengguna"
```

Docker: `docker compose up -d` → app on `:8080` (Apache, docroot `public/`), MySQL 8 on `:3306`, phpMyAdmin on `:8081`. `.env` is already pointed at the compose DB (`DB_HOST=db`). Artisan inside the container: `docker compose exec app php artisan ...`.

Tests run against **sqlite `:memory:`** (see `phpunit.xml`), not MySQL. Besides the two stock example tests there is `tests/Feature/AdminUserManagementTest.php`, which covers the admin user-browsing feature. Its last case calls `Sesi::factory()`, but no `SesiFactory` exists and `Sesi` does not use `HasFactory` — that test errors until one is added.

## Architecture

### Offline-first sync — the core constraint

The mobile app owns session identity: **`sesi.id` is a client-generated UUID v4**, never assigned by the server (`Sesi` has `$incrementing = false`, `id` is fillable). `PUT /sesi/{id}` (single) and `POST /sinkron` (batch) both funnel into `App\Services\Sinkron\PenggabungSesi::gabungkan()`, which is the **only** place merge/conflict rules live. Its four rules:

1. A `sampel` already `status = 'terisi'` is never overwritten.
2. Otherwise last-write-wins per session, compared against the client's `diperbarui_pada`.
3. Deletion beats update (a soft-deleted session can't be revived).
4. `waktu_tidak_pasti` is never un-set by the server (once true, always true).

New sessions get 4 sample slots pre-created at canonical offsets (`-1800, 0, +3600, +7200` seconds from `t0`) so `GET` always returns 4 elements. `GET /sinkron?sejak=&limit=` is a cursor pull keyed on `updated_at` (returns `kursor_berikutnya` + `ada_lagi`, includes soft-deleted rows via `withTrashed`).

If you change merge behavior, change it in `PenggabungSesi` — not in a controller.

### API response contract

- Success: Eloquent API Resources under `app/Http/Resources/Api/V1/`.
- Error: **every** `api/*` failure is serialized by the handlers in `bootstrap/app.php` as `{"galat": {"kode", "pesan", "detail?"}}`. `kode` comes from the fixed vocabulary in `App\Support\KodeGalat` — the mobile app branches on these strings, so adding codes is fine, redefining an existing one is not. Domain code throws `App\Exceptions\ApiException($kode, $pesan, $detail, $status)`.
- Timestamps: always through `App\Support\Waktu::iso()` (ISO-8601 UTC with microseconds). Never format dates ad hoc in a Resource.
- `App\Support\KodeAnalisis` is a separate vocabulary for *analysis job* outcomes, not HTTP errors.

### Authorization: two layers

`SesiPolicy` (explicit `$this->authorize(...)` in controllers) **plus** `App\Models\Scopes\OwnedByAuthUserScope`, a global scope on `Sesi`/`Kalibrasi`/etc. that adds `where user_id = auth()->id()` during HTTP requests and is skipped in console/queue context. Consequence: queue jobs and admin/console code that must see other users' rows call `Model::withoutGlobalScopes()` — `AnalisisNutrisiJob` and `PenggabungSesi` both do. Removing the explicit policy check because "the scope covers it" defeats the intent.

**Admin override.** `User::isAdmin()` is a hardcoded email comparison (`email === 'admin@asawatch.com'`) — there is no role column. It punches through *both* layers: `OwnedByAuthUserScope` returns early for an admin, and every `SesiPolicy` method starts with `$user->isAdmin() ||`. Neither is limited to `/admin/*`, so the same account authenticating via Sanctum gets the widened view on the API too (e.g. `GET /api/v1/sesi`, which relies on the scope rather than filtering by user, and `GET /api/v1/foto/{sesi}`). Keep that in mind before reusing `isAdmin()` in new places.

Rate limiters (`masuk`, `lupa-sandi`, `analisis`) are defined in `AppServiceProvider::boot()` and applied as `throttle:<name>` in `routes/api.php`.

### Photo + nutrition analysis pipeline

Photos are uploaded separately from the session (`POST /sesi/{sesi}/foto`) so a failed upload doesn't require resending the whole session. They go to the **private** `local` disk (`storage/app/private/foto/{user_id}/`) and are only readable through `GET /foto/{sesi}`, guarded by `signed` + `auth:sanctum` + policy — there is never a public URL to a food photo.

`POST /sesi/{sesi}/analisis` creates a `PekerjaanAnalisis` row and dispatches `AnalisisNutrisiJob`; the client polls `GET /sesi/{sesi}/analisis`. In the job:

- Results are cached by `foto_hash` (SHA-256 of file contents) — an identical photo reuses another session's `HasilDeteksi` instead of calling the provider.
- `hasil_deteksi.dikoreksi_user` wins: a late-finishing job must not overwrite a user-corrected result.
- Provider failure is a *status* (`pekerjaan.status = 'gagal'` + `kode_galat`), not a broken session.

`App\Services\Nutrisi\LayananVisionNutrisi` is an interface bound in `AppServiceProvider` to `StubLayananVisionNutrisi` — **no real vision provider is wired up yet**. Swapping in a real one should mean changing only that binding.

### Data model

`User` 1—1 `Profil`/`TargetHarian`, 1—* `Sesi`/`Kalibrasi`/`Perangkat`. `Sesi` 1—* `Sampel` (ordered by `index`), 1—1 `HasilDeteksi`, 1—* `ItemMakanan` (ordered by `urutan`), 1—* `PekerjaanAnalisis`. `User` and `Sesi` use `SoftDeletes` (deletion sync depends on this).

`Sampel` has a **composite primary key `(sesi_id, index)`**, which Eloquent does not support natively — always read/update it with explicit `where()` calls, never `find()`/`save()` on the model key.

### Admin UI

`resources/views/admin/` (Blade, extends `admin/layout.blade.php`, partials for sidebar/header/offcanvas) is the live UI. `public/admin/*.html` is the **original static Bootstrap prototype with dummy data** (see its own README) — kept as a design reference, not served as the app. Changes to admin styling still ride on `public/admin/assets/css/asawatch.css` + `assets/js/asawatch.js`, which the Blade layout loads; Bootstrap/Icons/fonts come from CDN. Pagination is configured for Bootstrap 5 (`Paginator::useBootstrapFive()`).

Most admin controllers are scoped to the logged-in user's own data, same as the API. The exception is `Admin\UserController` (`admin.users.index` / `users.show` / `users.session.show`), which is admin-only via `abort_unless(auth()->user()?->isAdmin(), 403)` and lists every other account, that account's sessions, and one session's detail. It reuses the `responden/detail` and `responden/show` views, switching their back-links through an `$isAdminView` flag; the sidebar entry and the "Mode Administrator" note are behind the same `isAdmin()` check. `Admin\ExportController` is the web twin of `GET /api/v1/akun/ekspor` (JSON + CSV) — a user-data-export right, not a reporting feature. Admin views deliberately show raw thresholds only; derived clinical metrics (e.g. "peak spike") belong to the mobile app, not this backend.

Vite (`resources/js`, `resources/css`, Tailwind 4) exists from the Laravel skeleton and is essentially unused by the admin UI.
