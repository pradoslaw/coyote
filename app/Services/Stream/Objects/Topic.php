<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Services\UrlBuilder;
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
        $this->displayName = $topic->title;
        $this->forum = ['id' => $topic->forum->id, 'name' => $topic->forum->name, 'slug' => $topic->forum->slug];

        if ($text) {
            $this->excerpt = excerpt($text);
        }

        return $this;
    }
}
