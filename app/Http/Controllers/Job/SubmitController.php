<?php

namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Country;
use Coyote\Currency;
use Coyote\Events\JobWasSaved;
use Coyote\Firm;
use Coyote\Firm\Benefit;
use Coyote\Job;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\FirmRepositoryInterface;
use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Repositories\Contracts\TagRepositoryInterface;
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
     * @var TagRepositoryInterface
     */
    private $tag;

    /**
     * SubmitController constructor.
     * @param JobRepositoryInterface $job
     * @param FirmRepositoryInterface $firm
     * @param TagRepositoryInterface $tag
     */
    public function __construct(JobRepositoryInterface $job, FirmRepositoryInterface $firm, TagRepositoryInterface $tag)
    {
        parent::__construct();

        $this->middleware('job.revalidate', ['except' => 'postTag']);
        $this->middleware('job.session', ['except' => ['getIndex', 'postIndex', 'postTag']]);

        $this->breadcrumb->push('Praca', route('job.home'));

        $this->job = $job;
        $this->firm = $firm;
        $this->tag = $tag;
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function getIndex(Request $request, $id = 0)
    {
        if ($request->session()->has('job')) {
            // get form content from session and fill model
            $job = $this->job->forceFill($request->session()->get('job'));
            $firm = $request->session()->get('firm');
        } else {
            $job = $this->job->findOrNew($id);
            $job->setDefaultUserId($this->userId);

            if ($job->id) {
                $job->city = $job->locations()->get()->implode('city', ', ');
                $job->deadline = (new Carbon($job->deadline_at))->diff(Carbon::now())->days;

                $job->tags = $job->tags()->get()->each(function (&$item, $key) {
                    $item->priority = $item->pivot->priority;
                });
            }

            // either load firm assigned to existing job offer or load user's default firm
            $firm = $this->loadFirm($job->firm_id);
            $job->firm_id = $firm->id; // it's really important to assign default firm id to job offer

            if (!empty($firm->id)) {
                $request->session()->put('firm', $firm->toArray());
            }
        }

        $this->authorize('update', $job);

        if (!empty($firm['id'])) {
            $this->authorize('update', $firm);
        }

        $request->session()->put('job', $job->toArray());

        $this->breadcrumb($job);
        $countryList = Country::lists('name', 'id');

        // @todo Uzyc mechanizmu geolokalizacji
        $defaultCountryId = array_search('Polska', $countryList->toArray());

        return $this->view('job.submit.home', [
            'countryList'       => $countryList,
            'defaultCountryId'  => $defaultCountryId,
            'currencyList'      => Currency::lists('name', 'id'),
            'employmentList'    => Job::getEmploymentList(),
            'rateList'          => Job::getRatesList(),
            'tagsList'          => $this->job->getPopularTags()
        ])->with(
            compact('job')
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postIndex(Request $request)
    {
        $this->validate($request, [
            'title'             => 'required|min:2|max:60',
            'country_id'        => 'required|integer',
            'currency_id'       => 'required|integer',
            'rate_id'           => 'required|integer',
            'employment_id'     => 'required|integer',
            'city'              => 'string',
            'salary_from'       => 'integer',
            'salary_to'         => 'integer',
            'deadline'          => 'integer|min:1|max:365',
            'requirements'      => 'string',
            'recruitment'       => 'sometimes|required|string',
            'enable_apply'      => 'boolean',
            'email'             => 'sometimes|required|email',
            'tags.*.name'       => 'tag',
            'tags.*.priority'   => 'int|min:0|max:1'
        ]);

        $request->session()->put('job', $request->all());

        if ($request->get('done')) {
            return $this->save($request);
        }

        return redirect()->route('job.submit.firm');
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function getFirm(Request $request)
    {
        // get all firms assigned to user...
        $firms = $this->getFirms();

        $job = $request->session()->get('job');
        $firm = $request->session()->get('firm');

        $this->breadcrumb($job);

        return $this->view('job.submit.firm', [
            'employeesList'     => Firm::getEmployeesList(),
            'foundedList'       => Firm::getFoundedList(),
            'benefitsList'      => Benefit::getBenefitsList(), // default benefits,
        ])->with(
            compact('job', 'firm', 'firms')
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postFirm(Request $request)
    {
        $this->validate($request, [
            'name'          => 'required_if:private,0|max:60',
            'is_agency'     => 'boolean',
            'logo'          => 'string',
            'website'       => 'url',
            'employees'     => 'integer',
            'founded'       => 'integer',
            'headline'      => 'string|max:100',
            'description'   => 'string',
            'latitude'      => 'numeric',
            'longitude'     => 'numeric',
            'street'        => 'string|max:255',
            'city'          => 'string|max:255',
            'house'         => 'string|max:50',
            'postcode'      => 'string|max:50'
        ], [
            'name.required_if' => 'Nazwa firmy jest wymagana'
        ]);

        // if offer is private, we MUST remove firm data from session
        if ($request->get('private')) {
            $request->session()->forget('firm');
            // very IMPORTANT: set firm id to null in case we don't want to associate firm with this offer
            $request->session()->put('job.firm_id', null);
        } else {
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
        $job = $request->session()->get('job');
        $this->breadcrumb($job);

        return $this->view('job.submit.preview');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $data = $request->session()->get('job');

        $job = $this->job->findOrNew((int) $data['id']);
        $job->setDefaultUserId($this->userId);

        $this->authorize('update', $job);

        $tags = [];
        if (!empty($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                $model = $this->tag->firstOrCreate(['name' => $tag['name']]);

                $tags[$model->id] = [
                    'priority' => $tag['priority']
                ];
            }
        }

        $locations = Job\Location::transformToArray($data['city']);
        $job->fill($data);

        $job->deadline_at = Carbon::now()->addDay($data['deadline']);

        \DB::transaction(function () use (&$job, $request, $locations, $tags) {
            if ($request->session()->has('firm.name')) {
                $data = $request->session()->get('firm');

                $firm = $this->firm->findOrNew((int) $data['id']);
                $firm->setDefaultUserId($this->userId);

                $this->authorize('update', $firm);

                $firm->fill($data)->save();
                $job->firm_id = $firm->id; // it's important to assign firm id to the offer

                $firm->benefits()->delete();
                $benefits = array_filter(array_unique(array_map('trim', $data['benefits'])));

                foreach ($benefits as $benefit) {
                    $firm->benefits()->create([
                        'name' => $benefit
                    ]);
                }
            }

            $job->save();
            $job->locations()->delete();

            foreach ($locations as $location) {
                $job->locations()->create([
                    'city' => $location
                ]);
            }

            $job->tags()->sync($tags);

            event(new JobWasSaved($job));
            $request->session()->forget(['job', 'firm']);
        });

        return redirect()->route('job.offer', [$job->id, $job->path])->with('success', 'Oferta została prawidłowo dodana.');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function postTag(Request $request)
    {
        $this->validate($request, ['name' => 'required|string|max:25|tag']);

        return view('job.submit.tag', [
            'tag' => [
                'name' => $request->name,
                'priority' => 1
            ]
        ]);
    }

    /**
     * @param $job
     */
    private function breadcrumb($job)
    {
        if (empty($job['id'])) {
            $this->breadcrumb->push('Wystaw ofertę pracy', route('job.submit'));
        } else {
            $this->breadcrumb->push($job['title'], route('job.offer', [$job['id'], $job['path']]));
            $this->breadcrumb->push('Edycja oferty', route('job.submit'));
        }
    }

    private function getFirms()
    {
        // get all firms assigned to user...
        return $this->firm->findAllBy('user_id', $this->userId);
    }

    /**
     * Load given firm from database or get defeault
     *
     * @param $firmId
     * @return mixed
     */
    private function loadFirm($firmId)
    {
        // it must be firm_id from "job" array (in case we are editing an offer)
        $firm = $this->firm->findOrNew((int) $firmId);
        $firm->setDefaultUserId($this->userId);

        $firms = $this->getFirms();

        if (empty($firm->id) && $firms->count()) {
            $firm = $firms->first();
        }

        $firm->benefits = $firm->benefits()->lists('name')->toArray();
        return $firm;
    }
}
