<?php

namespace Coyote\Http\Resources\Api;

use Carbon\Carbon;
use Coyote\Currency;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Http\Resources\FeatureResource;
use Coyote\Http\Resources\FirmResource;
use Coyote\Http\Resources\LocationResource;
use Coyote\Http\Resources\TagResource;
use Coyote\Job;
use Coyote\Services\UrlBuilder;
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
 * @property string $rate
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
     * @var \Coyote\Services\Parser\Factories\JobFactory
     */
    public static $parser;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = $this->resource->only(
            'id',
            'salary_from',
            'salary_to',
            'title',
            'is_remote',
            'remote_range',
            'is_gross',
            'rate',
            'employment',
            'seniority'
        )
        ;

        return array_merge($only, [
            'url'         => UrlBuilder::job($this->resource, true),
            'created_at'  => $this->created_at->toIso8601String(),
            'boost_at'    => $this->boost_at->toIso8601String(),
            'deadline_at' => $this->deadline_at->toIso8601String(),
            'locations'   => LocationResource::collection($this->locations),
            'tags'        => TagResource::collection($this->tags->sortByDesc('pivot.priority')),
            'currency'    => $this->currency->name,
            'description' => $this->parse((string) $this->description), // argument must be a string
            'recruitment' => $this->parse((string) $this->recruitment),

            'firm'        => $this->when($this->firm && $this->firm->exists, new FirmResource($this->firm)),

            'features'    => $this->whenLoaded('features', function () {
                return FeatureResource::collection($this->features);
            })
        ]);
    }

    /**
     * @param string $text
     * @return string
     */
    private function parse(string $text): string
    {
        if (!self::$parser) {
            self::$parser = app('parser.job');
        }

        return self::$parser->parse($text);
    }
}
