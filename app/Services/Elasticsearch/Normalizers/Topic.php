<?php

namespace Coyote\Services\Elasticsearch\Normalizers;

class Topic extends Normalizer
{
    /**
     * @return string
     */
    public function url()
    {
        return route('forum.topic', [$this->source['forum']['slug'], $this->source['id'], $this->source['slug']]);
    }

    /**
     * @return string
     */
    public function title()
    {
        return $this->getHighlight('subject');
    }

    /**
     * @return string
     */
    public function updatedAt()
    {
        return $this->hit['_source']['last_post_created_at'];
    }

    /**
     * @return string
     */
    public function excerpt()
    {
        return isset($this->hit['highlight']['text'])
            ? $this->hit['highlight']['text'][0]
            : (isset($this->source['posts'][0]) ? str_limit($this->source['posts'][0]['text'], 160) : '');
    }
}
