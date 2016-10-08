<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Forms\User\SkillsForm;
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

        $skills = $this->auth->skills()->get();

        return $this->view('user.skills.home')->with([
            'skills' => $skills,
            'form' => $this->getForm(),
            'rate_labels' => SkillsForm::RATE_LABELS
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function save(Request $request)
    {
        $form = $this->getForm();
        $skill = null;

        if ($form->isValid()) {
            $skill = $this->auth->skills()->create($request->all());
        }

        return view('user.skills.list')->with('item', $skill);
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

    /**
     * @return \Coyote\Services\FormBuilder\Form
     */
    protected function getForm()
    {
        return $this->createForm(SkillsForm::class, (object) array_only($this->auth->toArray(), ['id']), [
            'url' => route('user.skills')
        ]);
    }
}
