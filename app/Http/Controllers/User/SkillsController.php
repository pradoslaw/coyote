<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Requests\SkillsRequest;
use Coyote\Job\Preferences;
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

        $skills = $this->auth->skills()->get(['id', 'name', 'rate']);

        return $this->view('user.skills.home')->with([
            'skills' => $skills,
            'rate_labels' => SkillsRequest::RATE_LABELS
        ]);
    }

    /**
     * @param SkillsRequest $request
     * @return mixed
     */
    public function save(SkillsRequest $request)
    {
        $skill = $this->transaction(function () use ($request) {
            /** @var \Coyote\User\Skill $skill */
            $skill = $this->auth->skills()->create($request->all());

            $preferences = new Preferences($this->getSetting('job.preferences'));
            $preferences->addTag($skill->name);

            $this->setSetting('job.preferences', $preferences);

            return $skill;
        });

        return $skill->toJson();
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        $skill = $this->auth->skills()->findOrFail($id, ['id', 'user_id']);
        $skill->delete();
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
