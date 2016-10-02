<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\Topic as Model;

class Topic extends ObjectAbstract
{
    /**
     * @var array
     */
    public $forum;

    /**
     * @var string|null
     */
    public $excerpt;

    /**
     * @param Model $topic
     * @param string|null $text
     * @return $this
     */
    public function map(Model $topic, $text = null)
    {
        $this->id = $topic->id;
        $this->url = UrlBuilder::topic($topic);
        $this->displayName = $topic->subject;
        $this->forum = ['name' => $topic->forum->name, 'slug' => $topic->forum->slug];

        if ($text) {
            $this->excerpt = excerpt($text);
        }

        return $this;
    }
}
