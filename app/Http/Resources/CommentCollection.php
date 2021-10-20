<?php

namespace Coyote\Http\Resources;

use Coyote\Job;
use Coyote\Guide;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentCollection extends ResourceCollection
{
    /**
     * DO NOT REMOVE! This will preserver keys from being filtered in data
     *
     * @var bool
     */
    protected $preserveKeys = true;

    protected Guide|Job $owner;

    public function setOwner(Guide|Job $owner): static
    {
        $this->owner = $owner;

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
        $owner = (clone $this->owner)->unsetRelations();

        return $this
            ->collection
            ->map(function (CommentResource $resource) use ($request, $owner) {
                $comment = $resource->resource;
                $comment->setRelation('resource', $owner);

                foreach ($comment->children as $child) {
                    $child->setRelation('resource', $owner);
                }

                return $resource;
            })
            ->keyBy('id')
            ->toArray();
    }
}
