@extends('admin.layout')

@section('title', 'Daftar Responden · AsaWatch')
@section('page-title', 'Daftar Responden')
@section('page-subtitle', 'Semua responden yang terdaftar dalam sistem AsaWatch')

@section('content')

  <form method="GET" action="{{ route('admin.users.index') }}" class="hw-card d-flex align-items-center gap-3 flex-wrap" style="padding:1.1rem 1.35rem">
    <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width:400px">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius:12px 0 0 12px; border-color:#EDF6F1"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama atau email..." value="{{ $search }}" style="border-radius:0 12px 12px 0; border-color:#EDF6F1; font-size:.88rem">
      </div>
    </div>
    @if($search)
      <a href="{{ route('admin.users.index') }}" class="btn btn-hw-outline btn-sm">Bersihkan</a>
    @endif
    <button type="submit" class="btn btn-hw">Cari</button>
  </form>

  <div class="hw-card" style="padding:.5rem 1.35rem 1.1rem">
    <div class="table-responsive">
      <table class="table hw-table align-middle">
        <thead><tr>
          <th>Nama / Email</th><th>Perangkat</th><th>Sesi Selesai</th><th>Terdaftar</th><th></th>
        </tr></thead>
        <tbody>
          @forelse ($users as $user)
            @php
              $inisial = \Illuminate\Support\Str::of($user->nama)->explode(' ')->filter()->map(fn ($kata) => mb_substr($kata, 0, 1))->take(2)->implode('');
              $perangkat = $user->perangkat->first();
              $totalSesi = $user->sesi_count;
              $sesiSelesai = $user->sesi_selesai_count;
            @endphp
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <div class="hw-avatar fw-bold d-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:10px;font-size:.8rem;background:var(--hw-tint);color:var(--hw-green-600)">
                    {{ $inisial ?: '?' }}
                  </div>
                  <div>
                    <div class="fw-bold" style="font-size:.88rem">{{ $user->nama }}</div>
                    <div style="font-size:.75rem;color:var(--hw-muted)">{{ $user->email }}</div>
                  </div>
                </div>
              </td>
              <td>
                @if ($perangkat)
                  <span class="fw-semibold" style="font-size:.84rem;color:var(--hw-green-600)">{{ $perangkat->nama }}</span>
                  <div style="font-size:.75rem;color:var(--hw-muted)">Baterai: {{ $perangkat->baterai_terakhir !== null ? $perangkat->baterai_terakhir . '%' : '—' }}</div>
                @else
                  <span class="text-muted" style="font-size:.84rem">Belum ada</span>
                @endif
              </td>
              <td>
                <div class="fw-bold" style="font-size:.84rem">{{ $sesiSelesai }} <span class="fw-normal text-muted">/ {{ $totalSesi }}</span></div>
                <div class="progress mt-1" style="height:4px; max-width:80px; background-color:#EDF6F1">
                  <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalSesi ? ($sesiSelesai / $totalSesi) * 100 : 0 }}%"></div>
                </div>
              </td>
              <td style="font-size:.84rem;color:var(--hw-ink-2)">
                {{ $user->created_at->translatedFormat('d M Y') }}
              </td>
              <td class="text-end">
                <a class="btn btn-hw-outline btn-sm" href="{{ route('admin.users.show', $user) }}">Lihat Detail</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada responden ditemukan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 px-1">
      <div style="font-size:.8rem;color:var(--hw-muted)">Menampilkan {{ $users->count() }} dari {{ $users->total() }} responden</div>
      {{ $users->onEachSide(1)->links() }}
    </div>
  </div>

@endsection
