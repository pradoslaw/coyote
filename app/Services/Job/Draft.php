<?php

namespace Coyote\Services\Job;

use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Illuminate\Contracts\Auth\Guard;

class Draft
{
    /**
     * @var JobRepository
     */
    protected $job;

    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @param JobRepository $job
     * @param Guard $auth
     */
    public function __construct(JobRepository $job, Guard $auth)
    {
        $this->job = $job;
        $this->auth = $auth;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->job->getDraft($this->auth->id(), $key) !== null;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $data = $this->job->getDraft($this->auth->id(), $key);

        if ($data !== null) {
            return unserialize(base64_decode($data));
        }

        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function put(string $key, $value)
    {
        $this->job->setDraft($this->auth->id(), $key, base64_encode(serialize($value)));
    }

    /**
     * @return void
     */
    public function forget()
    {
        $this->job->forgetDraft($this->auth->id());
    }
}
