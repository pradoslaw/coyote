<?php
namespace Coyote\Http\Resources;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Services\Forum\Tracker;
use Coyote\Topic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    /**
     * DO NOT REMOVE! This will preserver keys from being filtered in data
     */
    protected bool $preserveKeys = true;

    private Tracker $tracker;
    private Topic $topic;
    private Forum $forum;
    private bool $obscureDeletedPosts = false;

    public function setTracker(Tracker $tracker): self
    {
        $this->tracker = $tracker;
        return $this;
    }

    public function setRelations(Topic $topic, Forum $forum): self
    {
        $this->topic = $topic;
        $this->forum = $forum;
        return $this;
    }

    public function obscureDeletedPosts(): void
    {
        $this->obscureDeletedPosts = true;
    }

    public function toArray(Request $request): array
    {
        $collection = $this
            ->collection
            ->map(function (PostResource $resource) {
                /** @var Post $post */
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

        if ($this->obscureDeletedPosts) {
            $collection->map(function (PostResource $resource) {
                $resource->obscureDeletedPosts();
                return $resource;
            });
        }

        return $this
            ->resource
            ->setCollection($collection)
            ->toArray();
    }
}
