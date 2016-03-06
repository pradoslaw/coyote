<?php

namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Country;
use Coyote\Currency;
use Coyote\Firm;
use Coyote\Firm\Benefit;
use Coyote\Job;
use Coyote\Http\Controllers\Controller;
use Coyote\Job\Employment;
use Coyote\Job\Rate;
use Coyote\Repositories\Contracts\FirmRepositoryInterface;
use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Illuminate\Http\Request;

class SubmitController extends Controller
{
    /**
     * @var JobRepositoryInterface
     */
    private $job;

    /**
     * @var FirmRepositoryInterface
     */
    private $firm;

    /**
     * SubmitController constructor.
     * @param JobRepositoryInterface $job
     * @param FirmRepositoryInterface $firm
     */
    public function __construct(JobRepositoryInterface $job, FirmRepositoryInterface $firm)
    {
        parent::__construct();

        $this->breadcrumb->push('Praca', route('job.home'));
        $this->breadcrumb->push('Wystaw ofertę pracy', route('job.submit'));

        $this->job = $job;
        $this->firm = $firm;
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function getIndex(Request $request, $id = 0)
    {
        $job = $this->job->findOrNew($id);

        if ($job->id) {
            $job->city = $job->locations()->get()->implode('name', ', ');
            $job->deadline = (new Carbon($job->deadline_at))->diff(Carbon::now())->days;
        }

        if ($request->session()->has('job')) {
            $job->forceFill($request->session()->get('job'));
        }

        $countryList = Country::lists('name', 'id');
        $currencyList = Currency::lists('name', 'id');
        $employmentList = Job::getEmploymentList();
        $rateList = Job::getRatesList();

        return $this->view('job.submit.home')->with(
            compact('job', 'countryList', 'currencyList', 'employmentList', 'rateList')
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postIndex(Request $request)
    {
        $this->validate($request, [
            'title'         => 'required|max:60',
            'country_id'    => 'required|integer',
            'currency_id'   => 'required|integer',
            'rate_id'       => 'required|integer',
            'employment_id' => 'required|integer',
            'city'          => 'string',
            'salary_from'   => 'integer',
            'salary_to'     => 'integer',
            'deadline'      => 'integer',
            'requirements'  => 'string',
            'recruitment'   => 'string',
            'enable_apply'  => 'boolean'
        ]);

        $request->session()->put('job', $request->all());

        return redirect()->route('job.submit.firm');
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function getFirm(Request $request)
    {
        $this->redirectIfSessionIsEmpty($request->session());

        $employeesList = Firm::getEmployeesList();
        $foundedList = Firm::getFoundedList();
        $benefitsList = Benefit::getBenefitsList();

        $job = $request->session()->get('job');
        $firm = $this->firm->find((int) $job['firm_id']);

        if ($firm->id) {
            $this->authorize('update', $firm);
        }

        return $this->view('job.submit.firm')->with(compact('job', 'firm', 'employeesList', 'foundedList', 'benefitsList'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postFirm(Request $request)
    {
        $this->redirectIfSessionIsEmpty($request->session());

        if (!$request->get('private')) {
            $this->validate($request, [
                'name' => 'required|max:60',
                'is_agency' => 'boolean',
                'logo' => 'string',
                'website' => 'url',
                'employees' => 'integer',
                'founded' => 'integer',
                'headline' => 'string|max:100',
                'description' => 'string',
                'latitude' => 'float',
                'longitude' => 'float',
                'street' => 'string|max:255',
                'city' => 'string|max:255',
                'house' => 'string|max:50',
                'postcode' => 'string|max:50'
            ], [
                'name.required' => 'Nazwa firmy jest wymagana'
            ]);

            $request->session()->put('firm', $request->all());
        }

        return redirect()->route('job.submit.preview');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPreview(Request $request)
    {
        $this->redirectIfSessionIsEmpty($request->session());

        return $this->view('job.submit.preview');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $this->redirectIfSessionIsEmpty($request->session());

        $data = $request->session()->get('job');

        $job = $this->job->findOrNew((int) $data['id']);
        if (!$job->id) {
            $job->user_id = $this->userId;
        }

        $this->authorize('update', $job);

        $locations = array_unique(array_map('trim', preg_split('/[\/,]/', $data['city'])));
        $job->fill($data);

        $job->deadline_at = Carbon::now()->addDay($data['deadline']);
        $job->path = str_slug($data['title'], '_');

        \DB::transaction(function () use (&$job, $request, $locations) {
            if ($request->session()->has('firm')) {
                $data = $request->session()->get('firm');
                $firm = $this->firm->firstOrNew(['user_id' => $this->userId, 'name' => $data['name']]);

                $firm->fill($data)->save();
                $job->firm_id = $firm->id;
            }

            $job->save();
            $job->locations()->delete();

            foreach ($locations as $location) {
                $job->locations()->create([
                    'name' => $location
                ]);
            }

            $request->session()->forget(['job', 'firm']);
        });

        return redirect()->route('job.offer', [$job->id, $job->path])->with('success', 'Oferta została prawidłowo dodana.');
    }

    /**
     * @param $session
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectIfSessionIsEmpty($session)
    {
        if (!$session->has('job')) {
            return redirect()->route('job.submit')->with('error', 'Przepraszamy, ale Twoja sesja wygasła po conajmniej 15 minutach nieaktywności.');
        }
    }
}
