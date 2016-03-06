<?php

namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\FirmRepositoryInterface;
use Coyote\Repositories\Contracts\JobRepositoryInterface ;
use Coyote\Firm;
use Coyote\Job;
use Coyote\Currency;
use Coyote\Country;

class OfferController extends Controller
{
    /**
     * @var JobRepositoryInterface
     */
    private $job;
    private $firm;

    public function __construct(JobRepositoryInterface $job, FirmRepositoryInterface $firm)
    {
        parent::__construct();

        $this->job = $job;
        $this->firm = $firm;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index($id, $slug)
    {
        $job = $this->job->findById($id);

        $this->breadcrumb->push('Praca', route('job.home'));
        $this->breadcrumb->push($job->title, route('job.offer', [$job->id, $job->path]));

        $parser = app('Parser\Job');

        foreach (['description', 'requirements', 'recruitment'] as $name) {
            if (!empty($job->$name)) {
                $job->$name = $parser->parse($job->$name);
            }
        }

        $locations = $job->locations()->lists('name')->map(function ($item) {
            return link_to('#', $item);
        });

        return $this->view('job.offer', [
            'ratesList'         => Job::getRatesList(),
            'employmentList'    => Job::getEmploymentList(),
            'employeesList'     => Firm::getEmployeesList(),
            'locations'         => $locations,
            'deadline'          => Carbon::parse($job->deadline_at)->diff(Carbon::now())->d
        ])->with(
            compact('job')
        );
    }
}
