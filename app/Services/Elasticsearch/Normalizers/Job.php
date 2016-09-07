<?php

namespace Coyote\Services\Elasticsearch\Normalizers;

class Job extends Normalizer
{
    /**
     * @return string
     */
    public function url()
    {
        return route('job.offer', [$this->source['id'], $this->source['slug']]);
    }

    /**
     * @return string
     */
    public function title()
    {
        return $this->getHighlight('title');
    }

    /**
     * @return string
     */
    public function excerpt()
    {
        return $this->getHighlight('description') ?: $this->getHighlight('requirements');
    }
}
