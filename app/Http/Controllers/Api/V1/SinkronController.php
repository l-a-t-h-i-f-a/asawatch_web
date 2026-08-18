<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SinkronRequest;
use App\Http\Resources\Api\V1\KalibrasiResource;
use App\Http\Resources\Api\V1\ProfilResource;
use App\Http\Resources\Api\V1\SesiResource;
use App\Models\Kalibrasi;
use App\Models\Profil;
use App\Services\Sinkron\PenggabungSesi;
use App\Support\Waktu;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SinkronController extends Controller
{
    public function index(Request $request)
    {
        $sejak = $request->query('sejak') ? Carbon::parse($request->query('sejak')) : Carbon::createFromTimestamp(0);
        $limit = min((int) $request->query('limit', 200), 500);
        $user = $request->user();

        $sesiDenganEkstra = $user->sesi()
            ->withTrashed()
            ->with(['sampel', 'hasilDeteksi.itemMakanan'])
            ->where('updated_at', '>', $sejak)
            ->orderBy('updated_at')
            ->limit($limit + 1)
            ->get();

        $adaLagi = $sesiDenganEkstra->count() > $limit;
        $sesiList = $sesiDenganEkstra->take($limit);

        $kalibrasiList = $user->kalibrasi()
            ->where('updated_at', '>', $sejak)
            ->orderBy('updated_at')
            ->get();

        $profil = $user->profil;
        $profilBerubah = $profil && $profil->updated_at->gt($sejak);

        $kursorBerikutnya = collect([$sesiList->max('updated_at'), $kalibrasiList->max('updated_at')])
            ->filter()
            ->max() ?? $sejak;

        return response()->json([
            'data' => [
                'sesi' => SesiResource::collection($sesiList),
                'kalibrasi' => KalibrasiResource::collection($kalibrasiList),
                'profil' => $profilBerubah ? new ProfilResource($profil) : null,
            ],
            'meta' => [
                'kursor_berikutnya' => Waktu::iso($kursorBerikutnya),
                'ada_lagi' => $adaLagi,
            ],
        ]);
    }

    public function store(SinkronRequest $request, PenggabungSesi $penggabung)
    {
        $user = $request->user();
        $data = $request->validated()['data'];

        $diterima = [];
        $ditolak = [];

        foreach ($data['sesi'] ?? [] as $item) {
            $hasil = $penggabung->gabungkan($user->id, $item);

            if ($hasil['status'] === 'diterima') {
                $diterima[] = $item['id'];
            } else {
                $ditolak[] = ['id' => $item['id'], 'kode' => $hasil['kode'], 'pesan' => $hasil['pesan']];
            }
        }

        foreach ($data['kalibrasi'] ?? [] as $item) {
            Kalibrasi::updateOrCreate(
                ['user_id' => $user->id, 'waktu' => $item['waktu']],
                array_merge($item, ['user_id' => $user->id]),
            );
        }

        if (! empty($data['profil'])) {
            Profil::updateOrCreate(['user_id' => $user->id], $data['profil']);
        }

        return response()->json([
            'data' => [
                'diterima' => $diterima,
                'ditolak' => $ditolak,
            ],
        ]);
    }
}
