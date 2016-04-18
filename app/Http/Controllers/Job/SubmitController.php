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
use Coyote\Services\Parser\Reference\City;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\GeoIp\Normalizers\Locale;

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
     * @var \Coyote\Services\GeoIp\GeoIp
     */
    private $geoIp;

    /**
     * SubmitController constructor.
     * @param JobRepositoryInterface $job
     * @param FirmRepositoryInterface $firm
     * @param TagRepositoryInterface $tag
     */
    public function __construct(JobRepositoryInterface $job, FirmRepositoryInterface $firm, TagRepositoryInterface $tag)
    {
        parent::__construct();

        $this->middleware('job.revalidate', ['except' => ['postTag', 'getFirmPartial']]);
        $this->middleware('job.session', ['except' => ['getIndex', 'postIndex', 'postTag', 'getFirmPartial']]);

        $this->breadcrumb->push('Praca', route('job.home'));

        $this->job = $job;
        $this->firm = $firm;
        $this->tag = $tag;

        $this->geoIp = app('GeoIp');

        $this->public['firm_partial'] = route('job.submit.firm.partial');
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
            $job = $this->job->forceFill((array) $request->session()->get('job'));
            $firm = $this->firm->forceFill((array) $request->session()->get('firm'));
        } else {
            $job = $this->job->findOrNew($id);
            $job->setDefaultUserId($this->userId);

            if ($job->id) {
                $job->city = $job->locations()->get()->implode('city', ', ');
                $job->deadline = (new Carbon($job->deadline_at))->diff(Carbon::now())->days;

                $job->tags = $job->tags()->get()->each(function (&$item, $key) {
                    $item->priority = $item->pivot->priority;
                });

                $firm = $this->loadFirm((int) $job->firm_id);
            } else {
                // either load firm assigned to existing job offer or load user's default firm
                $firm = $this->loadDefaultFirm();
            }

            $job->firm_id = $firm->id; // it's really important to assign default firm id to job offer

            if (!empty($firm->id)) {
                $request->session()->put('firm', $firm->toArray());
            }
        }

        $this->authorize('update', $job);

        if (!empty($firm->user_id)) {
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
            'popularTags'       => $this->job->getPopularTags()
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
            'city'              => 'string|city',
            'salary_from'       => 'integer',
            'salary_to'         => 'integer',
            'deadline'          => 'integer|min:1|max:365',
            'requirements'      => 'string',
            'recruitment'       => 'required_if:enable_apply,0|string',
            'enable_apply'      => 'boolean',
            'email'             => 'sometimes|required|email',
            'tags.*.name'       => 'tag',
            'tags.*.priority'   => 'int|min:0|max:1'
        ]);

        $userId = $request->session()->pull('job.user_id');
        $request->session()->put('job', $request->all() + ['user_id' => $userId]);

        return $this->next($request, redirect()->route('job.submit.firm'));
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function getFirm(Request $request)
    {
        $job = $request->session()->get('job');
        $firm = $request->session()->get('firm');

        // get all firms assigned to user...
        $firms = $this->getFirms($job['user_id']);

        $this->breadcrumb($job);

        return $this->view('job.submit.firm', $this->getFirmOptions())->with(
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
            $data = $request->all();
            $data['benefits'] = array_filter(array_unique(array_map('trim', $data['benefits'])));

            $request->session()->put('firm', $data);
        }

        return $this->next($request, redirect()->route('job.submit.preview'));
    }

    /**
     * AJAX request: get firm form edit
     *
     * @param Request $request
     * @param null $id
     * @return mixed
     */
    public function getFirmPartial(Request $request, $id = null)
    {
        $firm = null;

        if ($id) {
            $firm = $this->loadFirm($id);
            $this->authorize('update', $firm);

            $request->session()->put('firm', $firm->toArray());
        }

        return view('job.submit.partials.firm', $this->getFirmOptions())->with(compact('firm'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPreview(Request $request)
    {
        $job = $request->session()->get('job');
        $firm = $request->session()->get('firm');

        $this->breadcrumb($job);

        /*
         * We need to do a little transformation so data from session can look like data from database
         */
        $benefits = [];
        if (!empty($firm['benefits'])) {
            foreach ($firm['benefits'] as $benefit) {
                if (!empty($benefit)) {
                    $benefits[] = ['name' => $benefit];
                }
            }

            $firm['benefits'] = $benefits;
        }

        $tags = [];
        if (!empty($job['tags'])) {
            $tags = [Job\Tag::NICE_TO_HAVE => [], Job\Tag::MUST_HAVE => []];

            foreach ($job['tags'] as $tag) {
                $tags[$tag['priority']][] = [
                    'name' => $tag['name']
                ];
            }
        }

        $job['country_name'] = Country::find($job['country_id'])->name;

        if (!empty($job['city'])) {
            $job['locations'] = collect();
            $grabber = new City();

            foreach ($grabber->grab($job['city']) as $city) {
                $job['locations']->push(['city' => $city]);
            }
        }

        $parser = app('Parser\Job');

        foreach (['description', 'requirements', 'recruitment'] as $name) {
            if (!empty($job[$name])) {
                $job[$name] = $parser->parse($job[$name]);
            }
        }

        if ($firm['description']) {
            $firm['description'] = $parser->parse($firm['description']);
        }

        $deadline = new Carbon();
        $deadline->addDays($job['deadline']);

        return $this->view('job.submit.preview', [
            'ratesList'         => Job::getRatesList(),
            'employmentList'    => Job::getEmploymentList(),
            'deadline'          => $deadline->diff(Carbon::now())->days
        ])->with(
            compact('job', 'firm', 'tags')
        );
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

        $locations = (new City())->grab($data['city']);
        $job->fill($data);

        $job->deadline_at = Carbon::now()->addDay($data['deadline']);

        \DB::transaction(function () use (&$job, $request, $locations, $tags) {
            $activity = $job->id ? Stream_Update::class : Stream_Create::class;

            if ($request->session()->has('firm.name')) {
                $data = $request->session()->get('firm');

                $firm = $this->firm->findOrNew((int) $data['id']);
                $firm->setDefaultUserId($this->userId);

                $this->authorize('update', $firm);

                $firm->fill($data)->save();
                $job->firm_id = $firm->id; // it's important to assign firm id to the offer

                $firm->benefits()->delete();
//                $benefits = array_filter(array_unique(array_map('trim', $data['benefits'])));

                foreach ($data['benefits'] as $benefit) {
                    $firm->benefits()->create([
                        'name' => $benefit
                    ]);
                }
            }

            $job->save();
            $job->locations()->delete();

            foreach ($locations as $location) {
                $job->locations()->create(
                    $this->geocode($location)
                );
            }

            $job->tags()->sync($tags);

            event(new JobWasSaved($job));
            $request->session()->forget(['job', 'firm']);

            $parser = app('Parser\Job');
            $job->description = $parser->parse($job->description);

            stream($activity, (new Stream_Job)->map($job));
        });

        return redirect()->route('job.offer', [$job->id, $job->path])->with('success', 'Oferta została prawidłowo dodana.');
    }

    /**
     * @param $city
     * @return array
     */
    private function geocode($city)
    {
        $location = [
            'city'          => $city
        ];

        try {
            // @todo Ten mechanizm trzeba bedzie zmienic w przypadku angielskiej wersji serwisu
            $normalizer = new Locale(config('app.locale'));

            // we just want a first hit of a results with local name of the city
            // so Warsaw will become Warszawa
            $result = $normalizer->normalize($this->geoIp->city($city));

            $location = array_merge($location, [
                'latitude' => $result['latitude'],
                'longitude' => $result['longitude'],
                'city' => $result['name']
            ]);
        } catch (\Exception $e) {
            app('log')->error($e->getMessage());
        }

        return $location;
    }

    /**
     * @return array
     */
    private function getFirmOptions()
    {
        return [
            'employeesList'     => Firm::getEmployeesList(),
            'foundedList'       => Firm::getFoundedList(),
            'benefitsList'      => Benefit::getBenefitsList(), // default benefits,
        ];
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

    /**
     * @param Request $request
     * @param $next
     * @return \Illuminate\Http\RedirectResponse
     */
    private function next(Request $request, $next)
    {
        if ($request->get('done')) {
            return $this->save($request);
        }

        return $next;
    }

    /**
     * @param int $userId
     * @return mixed
     */
    private function getFirms($userId)
    {
        // get all firms assigned to user...
        return $this->firm->findAllBy('user_id', $userId);
    }

    /**
     * @param int $firmId
     * @return mixed
     */
    private function loadFirm($firmId)
    {
        $firm = $this->firm->findOrNew((int) $firmId);
        $firm->benefits = $firm->benefits()->lists('name')->toArray();

        return $firm;
    }

    /**
     * Load user's default firm
     *
     * @return \Coyote\Firm
     */
    private function loadDefaultFirm()
    {
        $firm = $this->firm->newInstance();
        $firms = $this->getFirms($this->userId);

        if ($firms->count()) {
            $firm = $firms->first();
        }

        $firm->benefits = $firm->benefits()->lists('name')->toArray();
        return $firm;
    }
}
