<?php

namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\FirmRepositoryInterface as FirmRepository;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\FlagRepositoryInterface as FlagRepository;
use Coyote\Firm;
use Coyote\Job;
use Coyote\Services\Elasticsearch\Factories\Job\MoreLikeThisFactory;

class OfferController extends Controller
{
    /**
     * @var JobRepository
     */
    private $job;

    /**
     * @var FirmRepository
     */
    private $firm;

    /**
     * OfferController constructor.
     * @param JobRepository $job
     * @param FirmRepository $firm
     */
    public function __construct(JobRepository $job, FirmRepository $firm)
    {
        parent::__construct();

        $this->job = $job;
        $this->firm = $firm;
    }

    /**
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function index($id)
    {
        // call method from repository to fetch job data and country name and currency
        $job = $this->job->findById($id);

        $this->breadcrumb->push('Praca', route('job.home'));
        $this->breadcrumb->push($job->title, route('job.offer', [$job->id, $job->slug]));

        $parser = app('parser.job');

        foreach (['description', 'requirements', 'recruitment'] as $name) {
            if (!empty($job->$name)) {
                $job->$name = $parser->parse($job->$name);
            }
        }

        $tags = $job->tags()->get()->groupBy('pivot.priority');

        $firm = [];
        if ($job->firm_id) {
            $firm = $this->firm->find($job->firm_id);
            $firm->description = $parser->parse($firm->description);
        }

        $job->increment('visits');
        $previous = url()->previous();

        if ($previous && mb_strlen($previous) < 200) {
            $referer = $job->referers()->firstOrNew(['url' => $previous]);

            if (!$referer->id) {
                $referer->save();
            } else {
                $referer->increment('count');
            }
        }

        if ($this->getGateFactory()->allows('job-delete')) {
            $flag = $this->getFlagFactory()->takeForJob($job->id);
        }

        $builder = (new MoreLikeThisFactory())->build($job);

        $build = $builder->build();
        debugbar()->debug($build);

        // search related topics
        $mlt = $this->job->search($build)->getSource();
        
        return $this->view('job.offer', [
            'ratesList'         => Job::getRatesList(),
            'employmentList'    => Job::getEmploymentList(),
            'employeesList'     => Firm::getEmployeesList(),
            'deadline'          => Carbon::parse($job->deadline_at)->diff(Carbon::now())->days,
            'subscribed'        => $this->userId ? $job->subscribers()->forUser($this->userId)->exists() : false
        ])->with(
            compact('job', 'firm', 'tags', 'flag', 'mlt')
        );
    }

    /**
     * @return FlagRepository
     */
    protected function getFlagFactory()
    {
        return app(FlagRepository::class);
    }
}
