<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Job;
use Coyote\Tag;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Job\Location[] $locations
 * @property Tag[] $tags
 * @property \Coyote\Firm $firm
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
        $only = $this->resource->only('id', 'title', 'firm', 'currency_symbol', 'is_remote', 'remote_range', 'score', 'subscribe_on');


//dd($only);
//        dd($this->resource);
        return array_merge($only, [
            'url'         => route('job.offer', [$this->resource['id'], $this->resource['slug']]),
            'boost_at'    => format_date($this->resource['boost_at']),
            'is_new'      => carbon($this->resource['boost_at'])->diffInDays(Carbon::now()) <= 2,
            'salary_from' => $this->money($this->resource['salary_from']),
            'salary_to'   => $this->money($this->resource['salary_to']),
            'rate_label'  => Job::getRatesList()[$this->resource['rate_id']] ?? null,
            'locations'   => $this->locations(),
            'tags'        => TagResource::collection($this->tags),
            'is_medal'    => $this->resource['score'] >= 150,
            'remote'      => [
                'range'         => $only['remote_range'],
                'enabled'       => $only['is_remote'],
                'url'           => route('job.remote')
            ],

            'firm'        => $this->firm ? new FirmResource($this->firm) : []
        ]);
    }

    /**
     * @return array
     */
    private function locations(): array
    {
        $result = [];

        foreach ($this->locations as $location) {
            $result[] = [
                'city'      => $location->city,
                'url'       => route('job.city', [$location->city])
            ];
        }

        return $result;
    }

    /**
     * @param float|null $number
     * @return string|null
     */
    private function money(?float $number): ?string
    {
        return $number ? number_format($number, 0, '', ' ') : null;
    }
}
