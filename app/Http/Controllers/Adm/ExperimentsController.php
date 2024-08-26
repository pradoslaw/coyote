<?php
namespace Coyote\Http\Controllers\Adm;

use Coyote\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExperimentsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb->push('Eksperymenty', route('adm.experiments'));
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
            'experimentsBackUrl'  => route('adm.experiments'),
            'experimentCreateUrl' => route('adm.experiments.create'),
            'experimentCsrfField' => '_token',
            'experimentCsrfToken' => $this->request->session()->token(),
        ]);
    }

    public function create(): RedirectResponse
    {
        $this->request->validate(['title' => 'required']);
        $survey = $this->newSurvey($this->request->get('title'));
        return redirect(route('adm.experiments.show', [$survey->id]));
    }

    public function show(Survey $survey): View
    {
        $this->breadcrumb->push($survey->title, '');
        return $this->view('adm.experiments.show', [
            'experimentsBackUrl'         => route('adm.experiments'),
            'experimentUpdateMembersUrl' => route('adm.experiments.updateMembers', ['survey' => $survey]),
            'experimentCsrfField'        => '_token',
            'experimentCsrfToken'        => $this->request->session()->token(),
            'experiment'                 => [
                'title'        => $survey->title,
                'creationDate' => "$survey->created_at",
                'userCount'    => $survey->users()->count(),
                'members'      => $survey->users()->pluck('id')->toArray(),
            ],
        ]);
    }

    private function newSurvey(string $title): Survey
    {
        /** @var Survey $survey */
        $survey = Survey::query()->create(['title' => $title]);
        return $survey;
    }

    public function updateMembers(Survey $survey): RedirectResponse
    {
        $survey->users()->sync(\array_filter($this->request->get('members', [])));
        return redirect(route('adm.experiments.show', [$survey->id]));
    }
}
