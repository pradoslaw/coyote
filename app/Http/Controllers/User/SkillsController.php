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
            'skills' => TagResource::collection($this->auth->skills),
            'rate_labels' => SkillsRequest::RATE_LABELS
        ]);
    }

    public function save(SkillsRequest $request)
    {
        $model = $this->transaction(function () use ($request) {
            $model = Tag::firstOrCreate(['name' => $request->input('name')]);

            $this->auth->skills()->attach($model->id, ['rate' => $request->input('priority'), 'order' => $request->input('order')]);

            return $model;
        });

        TagResource::withoutWrapping();

        return new TagResource($model);
    }

    public function update(Request $request, $id)
    {
        $this->auth->skills()->newPivotStatement()->where('tag_id', $id)->update(['rate' => $request->input('priority')]);
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        $this->auth->skills()->detach($id);
    }
}
