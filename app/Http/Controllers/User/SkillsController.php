<?php

namespace Coyote\Http\Controllers\User;

use Coyote\User;
use Illuminate\Http\Request;

class SkillsController extends BaseController
{
    use SettingsTrait;

    /**
     * @return $this
     */
    public function index()
    {
        $this->breadcrumb->push('Umiejętności', route('user.skills'));

        $skills = User\Skill::where('user_id', auth()->user()->id)->orderBy('order')->get();

        return $this->view('user.skills.home')->with('skills', $skills);
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function save(Request $request)
    {
        $userId = auth()->user()->id;

        $this->validate($request, [
            'name'              => 'required|string|max:100|unique:user_skills,name,NULL,id,user_id,' . $userId,
            'rate'              => 'required|integer|min:1|max:6'
        ], [
            'name.required'     => 'Proszę wpisać nazwę umiejętności',
            'name.unique'       => 'Taka umiejętność znajduje się już na Twojej liście.',
            'rate.min'          => 'Nie wprowadziłeś oceny swojej umiejętności.'
        ]);

        $skill = User\Skill::create($request->all() + ['user_id' => $userId]);
        return view('user.skills.list')->with('item', $skill);
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        $skill = User\Skill::findOrFail($id, ['id', 'user_id']);
        if ($skill->user_id !== auth()->user()->id) {
            abort(500);
        }

        $skill->delete();
    }

    /**
     * Saves order of skills
     *
     * @param Request $request
     */
    public function order(Request $request)
    {
        \DB::transaction(function () use ($request) {
            foreach ($request->get('order') as $id => $order) {
                \DB::table('user_skills')
                    ->where('id', $id)
                    ->where('user_id', auth()->user()->id)
                    ->update(['order' => intval($order) + 1]);
            }
        });
    }
}
