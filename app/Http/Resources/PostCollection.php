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
    private ?int $selectedPostId = null;

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

    public function setSelectedPostId(int $postId): void
    {
        $this->selectedPostId = $postId;
    }

    public function toArray(Request $request): array
    {
        /** @var PostResource $resource */
        foreach ($this->collection as $resource) {
            /** @var Post $post */
            $post = $resource->resource;
            // Set relations, to avoid N+1 SQL loading.
            // Be aware to use setRelation(), since setRelations() overwrites existing relations.
            $post->setRelation('topic', $this->topic);
            $post->setRelation('forum', $this->forum);
            $resource->setTracker($this->tracker);
            if ($this->obscureDeletedPosts) {
                $resource->obscureDeletedPosts();
            }
            if ($this->selectedPostId) {
                $resource->setSelectedPostId($this->selectedPostId);
            }
        }
        return $this
            ->resource
            ->setCollection($this->collection->keyBy('id'))
            ->toArray();
    }
}
