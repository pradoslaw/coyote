<?php

namespace Coyote\Http\Resources;

use Coyote\Microblog;

class MicroblogResource extends Api\MicroblogResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);

        TagResource::urlResolver(fn ($name) => route('microblog.tag', [urlencode($name)]));
    }

    public function toArray($request)
    {
        $result = parent::toArray($request);

        $assets = $result['media'];
        unset($result['media']);

        return array_merge_recursive($result, [
            'assets'        => $assets,
            'tags'          => $this->whenLoaded('tags', fn () => TagResource::collection($this->resource->tags)),
            'is_sponsored'  => $this->resource->is_sponsored,
            'metadata'      => encrypt([Microblog::class => $this->resource->id]),
            'deleted_at'    => $this->resource->deleted_at,

            'permissions'   => [
                'moderate'  => $this->when($request->user(), fn () => $request->user()->can('moderate', $this->resource), false)
            ],

            $this->mergeWhen($this->resource->relationLoaded('voters'), function () {
                $collection = $this->resource->voters->pluck('user.name');

                return [
                    'votes' => $collection->count(),
                    'voters' => $collection->when($collection->count() > 10, fn ($collection) => $collection->splice(0, 10)->concat(['...']))
                ];
            })
        ]);
    }
}
