<?php

namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Coyote\Repositories\Contracts\FirmRepositoryInterface;
use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Repositories\Contracts\FlagRepositoryInterface;
use Coyote\Firm;
use Coyote\Job;

class OfferController extends Controller
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
     * OfferController constructor.
     * @param JobRepositoryInterface $job
     * @param FirmRepositoryInterface $firm
     */
    public function __construct(JobRepositoryInterface $job, FirmRepositoryInterface $firm)
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
        $this->breadcrumb->push($job->title, route('job.offer', [$job->id, $job->path]));

        $parser = app('Parser\Job');

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

        return $this->view('job.offer', [
            'ratesList'         => Job::getRatesList(),
            'employmentList'    => Job::getEmploymentList(),
            'employeesList'     => Firm::getEmployeesList(),
            'deadline'          => Carbon::parse($job->deadline_at)->diff(Carbon::now())->days,
            'subscribed'        => $this->userId ? $job->subscribers()->forUser($this->userId)->exists() : false
        ])->with(
            compact('job', 'firm', 'tags', 'flag')
        );
    }

    /**
     * @return FlagRepositoryInterface
     */
    protected function getFlagFactory()
    {
        return app(FlagRepositoryInterface::class);
    }
}
