<?php

namespace Coyote\Services\Elasticsearch\Normalizers;

class Microblog extends Normalizer
{
    /**
     * @return string
     */
    public function url()
    {
        return route('microblog.view', [$this->source['id']]);
    }

    /**
     * @return string
     */
    public function title()
    {
        return str_limit($this->source['text']);
    }

    /**
     * @return string
     */
    public function excerpt()
    {
        return $this->getHighlight('text');
    }
}
