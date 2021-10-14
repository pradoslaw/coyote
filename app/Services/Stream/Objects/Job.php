<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Job as Model;
use Coyote\Services\UrlBuilder;

class Job extends ObjectAbstract
{
    /**
     * @param Model $job
     * @return $this
     */
    public function map(Model $job)
    {
        $this->id = $job->id;
        $this->url = UrlBuilder::job($job);
        $this->displayName = $job->title;

        return $this;
    }
}
