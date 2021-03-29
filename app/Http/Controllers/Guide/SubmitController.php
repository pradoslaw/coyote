<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\GuideRequest;
use Coyote\Http\Resources\GuideResource;
use Coyote\Models\Guide;

class SubmitController extends Controller
{
    public function index(Guide $guide, GuideRequest $request)
    {
        if (!$guide->exists) {
            $guide->user()->associate($this->auth);
        } else {
//            $this->authorize('update', $guide);
        }

        $guide->fill($request->all());

        $this->transaction(function () use ($guide) {
            $guide->save();
        });

        GuideResource::withoutWrapping();

        return new GuideResource($guide);

    }
}
