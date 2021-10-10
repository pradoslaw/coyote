<?php

namespace Coyote\Http\Resources;

use Coyote\Job;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentCollection extends ResourceCollection
{
    /**
     * DO NOT REMOVE! This will preserver keys from being filtered in data
     *
     * @var bool
     */
    protected $preserveKeys = true;

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
            ->map(function (CommentResource $resource) use ($request) {
//                $comment = $resource->resource;
//                $comment->job()->associate($model);
//
//                foreach ($comment->children as $child) {
//                    $child->job()->associate($model);
//                }

                return $resource;
            })
            ->keyBy('id');

        return $collection->toArray();
    }
}
