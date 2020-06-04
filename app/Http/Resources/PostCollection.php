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
     * @return $this
     */
    public function setTopic(Topic $topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * @param Forum $forum
     * @return $this
     */
    public function setForum(Forum $forum)
    {
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
        $parser = app('parser.sig');

        return $this
            ->resource
            ->setCollection(
                $this
                    ->collection
                    ->map(function (Post $post) use ($request, $parser) {
                        // set relations to avoid N+1 SQL loading
                        $post->setRelations(['topic' => $this->topic, 'forum' => $this->forum]);

                        return (new PostResource($post))->setTracker($this->tracker)->setSigParser($parser)->toArray($request);
                    })
            )
            ->toArray();
    }
}
