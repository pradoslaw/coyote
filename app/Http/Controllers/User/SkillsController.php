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
        $this->transaction(function () use ($request) {
            $model = Tag::firstOrCreate(['name' => $request->input('name')]);

            $this->auth->skills()->attach($model->id, ['rate' => $request->input('rate'), 'order' => $request->input('order')]);
        });
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        $this->auth->skills()->where('tag_id', $id)->delete();
    }

    /**
     * Saves order of skills
     *
     * @param Request $request
     */
    public function order(Request $request)
    {
        $this->transaction(function () use ($request) {
            foreach ($request->get('order') as $id => $order) {
                $this->auth->skills()->where('id', $id)->update(['order' => intval($order) + 1]);
            }
        });
    }
}
