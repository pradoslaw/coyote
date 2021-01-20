<?php

namespace Coyote\Http\Resources;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Services\Forum\Tracker;
use Coyote\Topic;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    /**
     * @var Tracker
     */
    protected $tracker;

    /**
     * @var Topic
     */
    protected $topic;

    /**
     * @var Forum
     */
    protected $forum;

    /**
     * DO NOT REMOVE! This will preserver keys from being filtered in data
     *
     * @var bool
     */
    protected $preserveKeys = true;

    /**
     * @param Tracker $tracker
     * @return $this
     */
    public function setTracker(Tracker $tracker)
    {
        $this->tracker = $tracker;

        return $this;
    }

    /**
     * @param Topic $topic
     * @param Forum $forum
     * @return $this
     */
    public function setRelations(Topic $topic, Forum $forum)
    {
        $this->topic = $topic;
        $this->forum = $forum;

        return $this;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $collection = $this
            ->collection
            ->map(function (PostResource $resource) use ($request) {
                $post = $resource->resource;

                // set relations to avoid N+1 SQL loading. be aware we must use setRelation() method because setRelations() overwrites all already
                // assigned relations
                $post->setRelation('topic', $this->topic);
                $post->setRelation('forum', $this->forum);

                $resource->resource = $post;
                $resource->setTracker($this->tracker);

                return $resource;
            })
            ->keyBy('id');

        return $this
            ->resource
            ->setCollection($collection)
            ->toArray();
    }
}
