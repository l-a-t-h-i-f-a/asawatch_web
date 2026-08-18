@extends('admin.layout')

@section('title', 'Riwayat Sesi · AsaWatch')
@section('page-title', 'Riwayat Sesi')
@section('page-subtitle', 'Daftar sesi makan yang tercatat dari aplikasi dan jam tangan')

@php
  $statusPill = fn ($status) => match ($status) {
      'selesai' => 'hw-pill-ok',
      'berjalan', 'menunggu_perangkat' => 'hw-pill-warn',
      'tidak_lengkap', 'dibatalkan' => 'hw-pill-bad',
      default => 'hw-pill-info',
  };
  $statusLabel = fn ($status) => match ($status) {
      'draft' => 'Draf',
      'menunggu_perangkat' => 'Menunggu Perangkat',
      'berjalan' => 'Berjalan',
      'selesai' => 'Selesai',
      'tidak_lengkap' => 'Tidak Lengkap',
      'dibatalkan' => 'Dibatalkan',
      default => ucfirst($status),
  };
@endphp

@section('content')

  <form method="GET" action="{{ route('admin.responden.index') }}" class="hw-card d-flex align-items-center gap-3 flex-wrap" style="padding:1.1rem 1.35rem">
    <div class="hw-seg">
      <button type="submit" name="status" value="" class="@if(!request('status')) active @endif">Semua</button>
      <button type="submit" name="status" value="selesai" class="@if(request('status')==='selesai') active @endif">Selesai</button>
      <button type="submit" name="status" value="berjalan" class="@if(request('status')==='berjalan') active @endif">Berjalan</button>
      <button type="submit" name="status" value="tidak_lengkap" class="@if(request('status')==='tidak_lengkap') active @endif">Tidak Lengkap</button>
    </div>
    <div class="flex-grow-1"></div>
    <a class="btn btn-hw d-flex align-items-center gap-2" href="{{ route('admin.ekspor.index') }}"><i class="bi bi-download"></i> Ekspor {{ $totalSesi }} sesi</a>
  </form>

  <div class="hw-card" style="padding:.5rem 1.35rem 1.1rem">
    <div class="table-responsive">
      <table class="table hw-table">
        <thead><tr>
          <th>Waktu Foto</th><th>t0</th><th>Status</th><th>Tak Pasti Waktu</th><th>Hasil Nutrisi</th><th></th>
        </tr></thead>
        <tbody>
          @forelse ($sesiList as $sesi)
            <tr>
              <td>
                <div class="fw-bold" style="font-size:.88rem">{{ $sesi->waktu_foto->translatedFormat('d M Y') }}</div>
                <div style="font-size:.75rem;color:var(--hw-muted)">{{ $sesi->waktu_foto->format('H:i') }}</div>
              </td>
              <td style="font-size:.84rem;color:var(--hw-ink-2)">{{ $sesi->t0?->format('H:i') ?? '—' }}</td>
              <td><span class="hw-pill {{ $statusPill($sesi->status) }}">{{ $statusLabel($sesi->status) }}</span></td>
              <td style="font-size:.84rem;color:var(--hw-ink-2)">{{ $sesi->waktu_tidak_pasti ? 'Ya' : '—' }}</td>
              <td style="font-size:.84rem;color:var(--hw-ink-2)">{{ $sesi->hasilDeteksi ? $sesi->hasilDeteksi->indeks_glikemik_perkiraan : 'Belum ada' }}</td>
              <td class="text-end"><a class="btn btn-hw-outline" href="{{ route('admin.responden.show', $sesi) }}">Lihat</a></td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada sesi tercatat.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 px-1">
      <div style="font-size:.8rem;color:var(--hw-muted)">Menampilkan {{ $sesiList->count() }} dari {{ $sesiList->total() }} sesi</div>
      {{ $sesiList->onEachSide(1)->links() }}
    </div>
  </div>

@endsection
