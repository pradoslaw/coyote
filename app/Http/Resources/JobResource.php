<?php
namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Currency;
use Coyote\Firm;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Job;
use Coyote\Job\Feature;
use Coyote\Services\UrlBuilder;
use Coyote\Tag;
use Illuminate\Database\Eloquent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $slug
 * @property Carbon $boost_at
 * @property Carbon $created_at
 * @property Job\Location[] $locations
 * @property Tag[]|Eloquent\Collection $tags
 * @property Firm $firm
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
 * @property Feature[] $features
 */
class JobResource extends JsonResource
{
    use MediaFactory;

    public function toArray(Request $request): array
    {
        $only = $this->resource->only('id', 'title', 'is_remote', 'remote_range', 'score', 'subscribe_on', 'comments_count', 'is_highlight', 'is_on_top', 'is_publish', 'is_gross');
        return [
            ...$only,
            'url'             => UrlBuilder::job($this->resource, true),
            'created_at'      => format_date($this->created_at, false),
            'boost_at'        => format_date($this->boost_at),
            'is_new'          => carbon($this->boost_at)->diffInDays(Carbon::now()) <= 2,
            'salary_from'     => $this->money($this->salary_from),
            'salary_to'       => $this->money($this->salary_to),
            'rate_label'      => Job::getRatesList()[$this->rate] ?? null,
            'locations'       => LocationResource::collection($this->locations),
            'tags'            => TagResource::collection($this->tags->sortByDesc('pivot.priority')),
            'is_medal'        => $this->score >= 150,
            'currency'        => $this->currency->name,
            'currency_symbol' => $this->currency->symbol,
            'remote'          => [
                'range'   => $this->remote_range,
                'enabled' => $this->is_remote,
                'url'     => route('job.remote'),
            ],
            'text'            => $this->description,
            'recruitment'     => $this->recruitment,
            'firm'            => $this->when($this->firm->exists, new FirmResource($this->firm), (object)['logo' => '', 'name' => '']),
            'features'        => $this->whenLoaded('features', function () {
                return FeatureResource::collection($this->features);
            }),
        ];
    }

    private function money(?float $number): ?float
    {
        return $number ? $this->resource->monthlySalary($number) : null;
    }
}
