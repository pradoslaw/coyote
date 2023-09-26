<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

class MicroblogCollection extends ResourceCollection
{
    /**
     * DO NOT REMOVE! This will preserve keys from being filtered in data
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
            ->map(function (MicroblogResource $resource) use ($request) {
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
