<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Services\Elasticsearch\Builders\Job\SearchBuilder;
use Illuminate\Http\Request;

abstract class BaseController extends Controller
{
    /**
     * @var JobRepository
     */
    protected $job;

    /**
     * @var SearchBuilder
     */
    protected $builder;

    /**
     * @param JobRepository $job
     */
    public function __construct(JobRepository $job)
    {
        parent::__construct();

        $this->job = $job;
    }
}
