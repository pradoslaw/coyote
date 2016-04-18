<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Job as Model;

class Job extends Object
{
    /**
     * @param Model $job
     * @return $this
     */
    public function map(Model $job)
    {
        $this->id = $job->id;
        $this->url = route('job.offer', [$job->id, $job->path], false);
        $this->displayName = excerpt($job->description);

        return $this;
    }
}
