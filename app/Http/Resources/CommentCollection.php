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

    public Job $job;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $model = clone $this->job;
        $model->unsetRelations();

        $collection = $this
            ->collection
            ->map(function (Job\Comment $comment) use ($request, $model) {
                $comment->job()->associate($model);

                foreach ($comment->children as $child) {
                    $child->job()->associate($model);
                }

                return new CommentResource($comment);
            })
            ->keyBy('id');

        return $collection->toArray();
    }
}
