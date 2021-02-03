<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Requests\SkillsRequest;
use Coyote\Http\Resources\TagResource;
use Coyote\Tag;
use Illuminate\Http\Request;

class SkillsController extends BaseController
{
    use SettingsTrait;

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Umiejętności', route('user.skills'));

        return $this->view('user.skills')->with([
            'skills' => TagResource::collection($this->auth->skills->load('category')),
            'rate_labels' => SkillsRequest::RATE_LABELS
        ]);
    }

    public function save(SkillsRequest $request): TagResource
    {
        $model = $this->transaction(function () use ($request) {
            $model = Tag::firstOrCreate(['name' => $request->input('name')]);

            $this->auth->skills()->attach($model->id, ['priority' => $request->input('priority'), 'order' => $request->input('order')]);

            return $model;
        });

        TagResource::withoutWrapping();

        return new TagResource($model->load('category'));
    }

    public function update(Request $request, int $id)
    {
        $this->auth->skills()->newPivotStatement()->where('user_id', $this->userId)->where('tag_id', $id)->update(['priority' => $request->input('priority')]);
    }

    public function delete(int $id)
    {
        $this->auth->skills()->detach($id);
    }
}
