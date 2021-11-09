<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Events\GuideSaved;
use Coyote\Http\Requests\GuideRequest;
use Coyote\Http\Resources\GuideResource;
use Coyote\Guide;
use Coyote\Services\Guide\RoleCalculator;

class SubmitController extends BaseController
{
    public function form()
    {
        $this->breadcrumb->push('Dodaj nowy post', route('guide.submit'));

        return $this->view('guide.form');
    }

    public function save(Guide $guide, GuideRequest $request)
    {
        $guide->exists && $this->authorize('update', $guide);

        $guide
            ->fill($request->all())
            ->creating(function (Guide $model) use ($request) {
                $model->user()->associate($this->auth);
                $model->role = $request->input('role');
            });

        $this->transaction(function () use ($guide, $request) {
            $guide->save();

            (new RoleCalculator($guide))->setRole($this->userId, $request->input('role'));

            $guide->setTags(array_pluck($request->input('tags', []), 'name'));
        });

        event(new GuideSaved($guide));

        GuideResource::withoutWrapping();

        return new GuideResource($guide);
    }
}
