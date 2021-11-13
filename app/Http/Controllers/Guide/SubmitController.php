<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Events\GuideDeleted;
use Coyote\Events\GuideSaved;
use Coyote\Http\Requests\GuideRequest;
use Coyote\Http\Resources\GuideResource;
use Coyote\Guide;
use Coyote\Services\Guide\RoleCalculator;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Guide as Stream_Guide;

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
            $guide->assets()->sync($request->input('assets'));

            (new RoleCalculator($guide))->setRole($this->userId, $request->input('role'));

            $guide->setTags(array_pluck($request->input('tags', []), 'name'));

            $object = (new Stream_Guide())->map($guide);

            if ($guide->wasRecentlyCreated) {
                // increase reputation points
                app('reputation.guide.create')->map($guide)->save();
                // put this to activity stream
                stream(Stream_Create::class, $object);
            } else {
                stream(Stream_Update::class, $object);
            }
        });

        event(new GuideSaved($guide));

        GuideResource::withoutWrapping();

        $guide->unsetRelation('assets');
        $guide->load(['assets', 'tags']);

        return new GuideResource($guide);
    }

    public function delete(Guide $guide): void
    {
        $this->authorize('delete', $guide);

        $this->transaction(function () use ($guide) {
            $guide->delete();
            // cofniecie pkt reputacji
            app('reputation.microblog.create')->undo($guide->id);

            // put this to activity stream
            stream(Stream_Delete::class, (new Stream_Guide())->map($guide));
        });

        event(new GuideDeleted($guide));

        session()->flash('success', 'Wpis został usunięty.');
    }
}
