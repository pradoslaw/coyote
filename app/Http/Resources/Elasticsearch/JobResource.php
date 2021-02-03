<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Coyote\Services\UrlBuilder;

/**
 * @property int $user_id
 * @property int $firm_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property \Carbon\Carbon $deadline_at
 * @property \Carbon\Carbon $boost_at
 * @property int $salary_from
 * @property int $salary_to
 * @method monthlySalary(float $salary): float
 * @property float $score
 * @property int $is_remote
 * @property int $remote_range
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string $recruitment
 * @property \Coyote\Firm $firm
 */
class JobResource extends ElasticsearchResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // maximum offered salary
        $salary = $this->monthlySalary(max($this->salary_from, $this->salary_to));
        $body = $this->resource->only(['id', 'slug', 'firm_id', 'is_remote', 'is_ads', 'is_on_top', 'user_id']);

        $locations = [];

        // We need to transform locations to format acceptable by elasticsearch.
        // I'm talking here about the coordinates
        /** @var \Coyote\Job\Location $location */
        foreach ($this->locations()->get() as $location) {
            $nested = ['city' => $location->city, 'label' => $location->label];

            if ($location->latitude && $location->longitude) {
                $nested['coordinates'] = [
                    'lat' => $location->latitude,
                    'lon' => $location->longitude
                ];
            }

            $locations[] = $nested;
        }

        // I don't know why elasticsearch skips documents with empty locations field when we use function_score.
        // That's why I add empty object (workaround).
        if (empty($locations)) {
            $locations[] = (object) [];
        }

        $body = array_merge($body, [
            'title'             => htmlspecialchars($this->title),
            'description'       => $this->stripTags($this->description),
            'recruitment'       => $this->stripTags($this->recruitment),
            'url'               => UrlBuilder::job($this->resource->getModel()),

            'created_at'        => $this->created_at->toIso8601String(),
            'updated_at'        => $this->updated_at->toIso8601String(),
            'boost_at'          => $this->boost_at->toIso8601String(),
            'deadline_at'       => $this->deadline_at->toIso8601String(),
            'decay_date'        => $this->boost_at->toIso8601String(),

            // score must be int
            'score'             => (int) $this->score,
            'locations'         => $locations,
            'salary'            => $salary,
            'salary_from'       => $this->monthlySalary($this->salary_from),
            'salary_to'         => $this->monthlySalary($this->salary_to),
            // yes, we index currency name so we don't have to look it up in database during search process
            'currency_symbol'   => $this->currency()->value('symbol'),
            // higher tag's priorities first
            'tags'              => $this->tags()->get(['name', 'priority'])->sortByDesc('pivot.priority')->pluck('name')->toArray(),
            // index null instead of 100 is job is not remote
            'remote_range'      => $this->is_remote ? $this->remote_range : null,

            'suggest'           => $this->getSuggest(),
            'subscribers'       => $this->subscribers()->pluck('user_id'),

            'firm'              => $this->when($this->firm_id, fn () => new FirmResource($this->firm))
        ]);

        return $body;
    }

    protected function getDefaultSuggestTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    protected function weight(): int
    {
        return round(($this->score * 20) + (($this->created_at->timestamp - self::BASE_TIMESTAMP) / 600000));
    }

    /**
     * @return array
     */
    protected function categories(): array
    {
        return ['user:' . $this->user_id];
    }

    /**
     * @param string $value
     * @return string
     */
    private function stripTags($value)
    {
        // w oferach pracy, edytor tinymce nie dodaje znaku nowej linii. zamiast tego mamy <br />. zamieniamy
        // na znak nowej linii aby poprawnie zindeksowac tekst w elasticsearch. w przeciwnym przypadku
        // teks foo<br />bar po przepuszczeniu przez stripHtml() zostalby zamieniony na foobar co niepoprawnie
        // zostaloby zindeksowane jako jeden wyraz
        return strip_tags(str_replace(['<br />', '<br>'], "\n", $value));
    }
}
