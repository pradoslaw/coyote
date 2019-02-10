<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Job;
use Illuminate\Http\Resources\Json\JsonResource;

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
        $only = $this->resource->only('id', 'title', 'firm', 'currency_symbol', 'is_remote', 'remote_range')->toArray();
//dd($this->resource);
        return array_merge($only, [
            'url'         => route('job.offer', [$this->resource['id'], $this->resource['slug']]),
            'boost_at'    => format_date($this->resource['boost_at']),
            'is_new'      => carbon($this->resource['boost_at'])->diffInDays(Carbon::now()) <= 2,
            'salary_from' => $this->money($this->resource['salary_from']),
            'salary_to'   => $this->money($this->resource['salary_to']),
            'rate_label'  => Job::getRatesList()[$this->resource['rate_id']] ?? null,
            'locations'   => $this->locations(),
            'remote'      => [
                'range'         => $only['remote_range'],
                'enabled'       => $only['is_remote'],
                'url'           => route('job.remote')
            ],

            'firm'        => [
                'name'          => array_get($only, 'firm.name'),
                'logo'          => (string) $this->getMediaFactory()->make('logo', ['file_name' => array_get($only, 'firm.logo')])->url(),
                'url'           => route('job.firm', array_get($only, 'firm.slug'))
            ]
        ]);
    }

    /**
     * @return array
     */
    private function locations(): array
    {
        $result = [];

        foreach ($this->resource['locations'] as $location) {
            $result[] = [
                'city'      => $location->get('city'),
                'url'       => route('job.city', [$location->get('city')])
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
