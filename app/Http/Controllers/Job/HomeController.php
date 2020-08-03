<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Resources\JobCollection;
use Coyote\Http\Resources\JobResource;
use Coyote\Http\Resources\TagResource;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\EagerLoadingWithCount;
use Coyote\Repositories\Criteria\Job\IncludeSubscribers;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Coyote\Repositories\Criteria\Tag\ForCategory;
use Coyote\Services\Elasticsearch\Builders\Job\SearchBuilder;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Tag;
use Illuminate\Http\Request;
use Coyote\Currency;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HomeController extends BaseController
{
    /**
     * @var TagRepository
     */
    private $tag;

    /**
     * @var string
     */
    private $firmName;

    /**
     * @param JobRepository $job
     * @param TagRepository $tag
     */
    public function __construct(JobRepository $job, TagRepository $tag)
    {
        parent::__construct($job);
        $this->tag = $tag;

        $this->middleware(function (Request $request, $next) {
            $this->builder = new SearchBuilder($request);

            return $next($request);
        });
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return $this->load();
    }

    /**
     * @param $name
     * @return \Illuminate\View\View
     */
    public function city($name)
    {
        $this->builder->city->addCity($name);

        return $this->load();
    }

    /**
     * @param $name
     * @return \Illuminate\View\View
     */
    public function tag($name)
    {
        $this->builder->tag->addTag(Str::lower($name));

        return $this->load();
    }

    /**
     * @param $slug
     * @return \Illuminate\View\View
     */
    public function firm($slug)
    {
        $this->builder->addFirmFilter($slug);
        $this->firmName = $slug;

        return $this->load();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function remote()
    {
        $this->builder->addRemoteFilter();

        return $this->load();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function my()
    {
        $this->builder->addUserFilter($this->userId);

        return $this->load();
    }

    /**
     * @return \Illuminate\View\View
     */
    private function load()
    {
        // set sort by score if keyword was provided and no sort was specified
        $defaultSort = $this->request->input('sort', $this->request->filled('q') ? SearchBuilder::SCORE : SearchBuilder::DEFAULT_SORT);

        $this->builder->boostLocation($this->request->attributes->get('geocode'));
        $this->builder->setSort($defaultSort);

        $result = $this->job->search($this->builder);

        // keep in mind that we return data by calling getSource(). This is important because
        // we want to pass collection to the twig (not raw php array)
        /** @var Collection $source */
        $source = $result->getSource();

        ///////////////////////////////////////////////////////////////////

        $eagerCriteria = new EagerLoading(['firm', 'locations', 'tags', 'currency']);

        $this->job->pushCriteria($eagerCriteria);
        $this->job->pushCriteria(new EagerLoadingWithCount(['comments']));
        $this->job->pushCriteria(new IncludeSubscribers($this->userId));

        $jobs = [];

        if (count($source)) {
            $premium = $result->getAggregationHits('premium_listing', true);
            $premium = array_first($premium); // only one premium at the top

            if ($premium) {
                $source->prepend($premium);
            }

            $ids = $source->pluck('id')->unique()->toArray();
            $jobs = $this->job->findManyWithOrder($ids);
        }

        $pagination = new LengthAwarePaginator(
            $jobs,
            $result->total(),
            SearchBuilder::PER_PAGE,
            LengthAwarePaginator::resolveCurrentPage(),
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        $pagination->appends($this->request->except('page'));

        $this->job->resetCriteria();

        $this->job->pushCriteria($eagerCriteria);
        $this->job->pushCriteria(new PriorDeadline());

        // get only tags belong to specific category
        $this->tag->pushCriteria(new ForCategory(Tag\Category::LANGUAGE));

        // only tags with logo
        $tags = $this->tag->all()->filter(function (Tag $tag) {
            return $tag->logo->getFilename() !== null;
        });

        $input = array_merge(
            $this->request->all('q', 'city', 'sort', 'salary', 'currency', 'remote_range', 'page'),
            [
                'tags'          => $this->builder->tag->getTags(),
                'locations'     => $this->builder->city->getCities(),
                'remote'        => $this->request->filled('remote') || $this->request->route()->getName() === 'job.remote' ? true : null,
            ]
        );

        $data = [
            'input'             => $input,
            'url'               => $this->fullUrl($this->request->except('json')),

            'defaults'           => [
                'sort'                  => $defaultSort,
                'currency'              => Currency::PLN
            ],

            'locations'         => $result->getAggregationCount("global.locations.locations_city_original")->slice(0, 10)->filter(),
            'tags'              => TagResource::collection($tags)->toArray($this->request),
            'jobs'              => json_decode((new JobCollection($pagination))->response()->getContent()),
            'subscribed'        => $this->getSubscribed(),
            'published'         => $this->getPublished()
        ];

        $this->request->session()->put('current_url', $this->request->fullUrl());

        if ($this->request->wantsJson()) {
            return response()->json($data);
        }

        return $this->view('job.home', $data + [
            'currencies'    => (object) Currency::all('name', 'id', 'symbol')->keyBy('id'),
            'form_url'      => $this->request->url(),
            'firm'          => $this->firmName
        ]);
    }

    /**
     * @return array
     */
    private function getSubscribed(): array
    {
        if (!$this->userId) {
            return [];
        }

        return JobResource::collection($this->job->subscribes($this->userId))->toArray($this->request);
    }

    /**
     * @return array
     */
    public function getPublished(): array
    {
        if (!$this->userId) {
            return [];
        }

        return JobResource::collection($this->job->getPublished($this->userId))->toArray($this->request);
    }

    /**
     * @param array $query
     * @return string
     */
    private function fullUrl(array $query): string
    {
        $question = $this->request->getBaseUrl() . $this->request->getPathInfo() === '/' ? '/?' : '?';

        return $this->request->url() . (count($query) ? ($question . Arr::query($query)) : '');
    }
}
