<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Currency;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Job;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\Tag;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $slug
 * @property Carbon $boost_at
 * @property Carbon $created_at
 * @property Carbon $deadline_at
 * @property Job\Location[] $locations
 * @property Tag[] $tags
 * @property \Coyote\Firm $firm
 * @property float $salary_from
 * @property float $salary_to
 * @property Currency $currency
 * @property int $rate_id
 * @property bool $is_highlight
 * @property int $score
 * @property bool $is_remote
 * @property int $remote_range
 * @property string $description
 * @property string $recruitment
 * @property \Coyote\Job\Feature[] $features
 */
class JobApiResource extends JsonResource
{
    use MediaFactory;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = $this->resource->only('id', 'title', 'is_remote', 'remote_range', 'is_gross');

        return array_merge($only, [
            'url'         => UrlBuilder::job($this->resource, true),
            'created_at'  => $this->created_at->toIso8601String(),
            'boost_at'    => $this->boost_at->toIso8601String(),
            'deadline_at' => $this->deadline_at->toIso8601String(),
            'locations'   => LocationResource::collection($this->locations),
            'tags'        => TagResource::collection($this->tags->sortByDesc('pivot.priority')),
            'currency'    => $this->currency->name,
            'firm'        => new FirmResource($this->firm),

            'features'    => $this->whenLoaded('features', function () {
                return FeatureResource::collection($this->features);
            })
        ]);
    }
}
