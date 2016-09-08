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
        return isset($this->hit['highlight']['long_title'])
            ? $this->getHighlight('long_title')
            : $this->getHighlight('title');
    }

    /**
     * @return string
     */
    public function excerpt()
    {
        return isset($this->hit['highlight']['excerpt']) ? $this->getHighlight('excerpt') : $this->getHighlight('text');
    }
}
