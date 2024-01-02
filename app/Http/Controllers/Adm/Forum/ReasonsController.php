<?php

namespace Coyote\Http\Controllers\Adm\Forum;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Forum\Reason;
use Coyote\Http\Controllers\Adm\BaseController;
use Coyote\Http\Forms\ForumReasonsForm;
use Coyote\Http\Grids\Adm\Forum\ReasonsGrid;

class ReasonsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->breadcrumb->pushMany([
            'Forum'            => route('adm.forum.categories'),
            'Powody moderacji' => route('adm.forum.reasons'),
        ]);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $grid = $this
            ->gridBuilder()
            ->createGrid(ReasonsGrid::class)
            ->setSource(new EloquentSource(new Reason()))
            ->setEnablePagination(false);

        return $this->view('adm.forum.reasons.home')->with('grid', $grid);
    }

    /**
     * @param int|null $id
     * @return \Illuminate\View\View
     */
    public function edit($id = null)
    {
        $reason = Reason::findOrNew($id);
        $this->breadcrumb->push($reason->name ?? 'Dodaj nowy', route('adm.forum.reasons.save'));

        return $this->view('adm.forum.reasons.save')->with('form', $this->getForm($reason));
    }

    /**
     * @param int|null $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($id = null)
    {
        $reason = Reason::findOrNew($id);

        $form = $this->getForm($reason);
        $form->validate();

        $reason->fill($form->all())->save();

        return redirect()->route('adm.forum.reasons')->with('success', 'Zmiany zostały zapisane.');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        Reason::destroy($id);

        return redirect()->route('adm.forum.reasons')->with('success', 'Rekord został usunięty.');
    }

    /**
     * @param Reason $reason
     * @return \Coyote\Services\FormBuilder\Form
     */
    private function getForm($reason)
    {
        return $this->createForm(ForumReasonsForm::class, $reason);
    }
}
