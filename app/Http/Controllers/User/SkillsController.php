<?php

namespace Coyote\Http\Controllers\User;

use Coyote\User;
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

        $skills = auth()->user()->skills()->orderBy('order')->get();

        return $this->view('user.skills.home')->with('skills', $skills);
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function save(Request $request)
    {
        $this->validate($request, [
            'name'              => 'required|string|max:100|unique:user_skills,name,NULL,id,user_id,' . $this->userId,
            'rate'              => 'required|integer|min:1|max:6'
        ], [
            'name.required'     => 'Proszę wpisać nazwę umiejętności',
            'name.unique'       => 'Taka umiejętność znajduje się już na Twojej liście.',
            'rate.min'          => 'Nie wprowadziłeś oceny swojej umiejętności.'
        ]);

        $skill = auth()->user()->skills()->create($request->all());
        return view('user.skills.list')->with('item', $skill);
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        $skill = auth()->user()->skills()->findOrFail($id, ['id', 'user_id']);
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
                auth()->user()->skills()->where('id', $id)->update(['order' => intval($order) + 1]);
            }
        });
    }
}
