<?php

namespace Coyote\Http\Resources;

use Coyote\Microblog;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

class MicroblogCollection extends ResourceCollection
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
            ->map(function (Microblog $microblog) use ($request) {
                $resource = new MicroblogResource($microblog);
                $resource->preserverKeys();

                return $resource->toArray($request);
            })
            ->keyBy('id');

        if ($this->resource instanceof AbstractPaginator) {
            return $this->resource->setCollection($collection)->toArray();
        }

        return $collection->toArray();
    }
}
