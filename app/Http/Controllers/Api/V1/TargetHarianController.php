<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TargetHarianRequest;
use App\Http\Resources\Api\V1\TargetHarianResource;
use App\Models\TargetHarian;
use Illuminate\Http\Request;

class TargetHarianController extends Controller
{
    public function show(Request $request)
    {
        $target = $request->user()->targetHarian ?? new TargetHarian(['user_id' => $request->user()->id]);

        return new TargetHarianResource($target);
    }

    public function update(TargetHarianRequest $request)
    {
        $target = TargetHarian::updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated(),
        );

        return new TargetHarianResource($target);
    }
}
