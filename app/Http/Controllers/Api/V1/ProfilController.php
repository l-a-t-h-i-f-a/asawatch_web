<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProfilRequest;
use App\Http\Resources\Api\V1\ProfilResource;
use App\Models\Profil;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $profil = $user->profil ?? (new Profil(['user_id' => $user->id]))->setRelation('user', $user);

        return new ProfilResource($profil);
    }

    public function update(ProfilRequest $request)
    {
        $user = $request->user();

        $profil = Profil::updateOrCreate(
            ['user_id' => $user->id],
            $request->validated(),
        );

        $profil->setRelation('user', $user);

        return new ProfilResource($profil);
    }
}
