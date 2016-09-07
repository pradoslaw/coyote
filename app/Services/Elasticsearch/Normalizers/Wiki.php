<?php

namespace Coyote\Services\Elasticsearch\Normalizers;

class Wiki extends Normalizer
{
    /**
     * @return string
     */
    public function url()
    {
        return url($this->source['path']);
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
        return $this->getHighlight('text') ?: $this->getHighlight('excerpt');
    }
}
