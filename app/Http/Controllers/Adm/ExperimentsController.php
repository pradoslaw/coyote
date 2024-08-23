<?php
namespace Coyote\Http\Controllers\Adm;

use Coyote\Models\Survey;
use Illuminate\View\View;

class ExperimentsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb->push('Eksperymenty', '');
    }

    public function index(): View
    {
        return $this->view('adm.experiments.home', [
            'experimentNewUrl' => route('adm.experiments.edit'),
            'experiments'      => $this->experiments(),
        ]);
    }

    private function experiments(): array
    {
        return Survey::query()->get()
            ->map(fn(Survey $survey) => [
                'title' => $survey->title,
                'url'   => route('adm.experiments.show', $survey->id),
            ])
            ->toArray();
    }

    public function edit(): View
    {
        $this->breadcrumb->push('Nowy', '');
        return $this->view('adm.experiments.edit', [
            'experimentsBackUrl' => route('adm.experiments'),
        ]);
    }

    public function show(Survey $survey): View
    {
        return $this->view('adm.experiments.show', [
            'experimentsBackUrl' => route('adm.experiments'),
            'experiment'         => [
                'title'        => $survey->title,
                'creationDate' => "$survey->created_at",
                'userCount'    => $survey->users()->count(),
            ],
        ]);
    }
}
