<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\FlagFactory;
use Coyote\Repositories\Contracts\FirmRepositoryInterface as FirmRepository;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Firm;
use Coyote\Job;
use Coyote\Services\Elasticsearch\Builders\Job\MoreLikeThisBuilder;

class OfferController extends Controller
{
    use FlagFactory;

    /**
     * @var JobRepository
     */
    private $job;

    /**
     * @var FirmRepository
     */
    private $firm;

    /**
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
     * @param \Coyote\Job $job
     * @return \Illuminate\View\View
     */
    public function index($job)
    {
        $this->breadcrumb->push('Praca', route('job.home'));
        $this->breadcrumb->push($job->title, route('job.offer', [$job->id, $job->slug]));

        $parser = app('parser.job');

        foreach (['description', 'requirements', 'recruitment'] as $name) {
            if (!empty($job->{$name})) {
                $job->{$name} = $parser->parse($job->{$name});
            }
        }

        $tags = $job->tags()->get()->groupBy('pivot.priority');

        $firm = [];
        if ($job->firm_id) {
            $firm = $this->firm->find($job->firm_id);
            $firm->description = $parser->parse((string) $firm->description);
        }

        $job->increment('views');
        $job->addReferer(url()->previous());

        if ($this->getGateFactory()->allows('job-delete')) {
            $flag = $this->getFlagFactory()->takeForJob($job->id);
        }

        // search related topics
        $mlt = $this->job->search(new MoreLikeThisBuilder($job))->getSource();

        return $this->view('job.offer', [
            'rates_list'        => Job::getRatesList(),
            'employment_list'   => Job::getEmploymentList(),
            'employees_list'    => Firm::getEmployeesList(),
            'seniority_list'    => Job::getSeniorityList(),
            'subscribed'        => $this->userId ? $job->subscribers()->forUser($this->userId)->exists() : false,
            'applied'           => $job->hasApplied($this->userId, $this->sessionId)
        ])->with(
            compact('job', 'firm', 'tags', 'flag', 'mlt')
        );
    }
}
