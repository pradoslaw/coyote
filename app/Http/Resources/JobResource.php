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
 */
class JobResource extends JsonResource
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
        $only = $this->resource->only('id', 'title', 'firm', 'is_remote', 'remote_range', 'score', 'subscribe_on', 'comments_count', 'is_highlight', 'is_on_top');

        return array_merge($only, [
            'url'         => UrlBuilder::job($this->resource),
            'boost_at'    => format_date($this->boost_at),
            'is_new'      => carbon($this->boost_at)->diffInDays(Carbon::now()) <= 2,
            'salary_from' => $this->money($this->salary_from),
            'salary_to'   => $this->money($this->salary_to),
            'rate_label'  => Job::getRatesList()[$this->rate_id] ?? null,
            'locations'   => LocationResource::collection($this->locations),
            'tags'        => TagResource::collection($this->tags->sortByDesc('pivot.priority')),
            'is_medal'    => $this->score >= 150,
            'currency_symbol' => $this->currency->symbol,
            'remote'      => [
                'range'         => $this->remote_range,
                'enabled'       => $this->is_remote,
                'url'           => route('job.remote')
            ],

            'firm'        => $this->firm ? new FirmResource($this->firm) : (object) ['logo' => '', 'name' => '']
        ]);
    }

    /**
     * @param float|null $number
     * @return string|null
     */
    private function money(?float $number): ?string
    {
        return $number ? number_format($this->resource->monthlySalary($number), 0, '', ' ') : null;
    }
}
