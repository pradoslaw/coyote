<?php

namespace Coyote;

use Coyote\Services\Elasticsearch\CharFilters\JobFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property int $firm_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property \Carbon\Carbon $deadline_at
 * @property int $salary_from
 * @property int $salary_to
 * @property int $country_id
 * @property int $currency_id
 * @property int $is_remote
 * @property int $enable_apply
 * @property int $visits
 * @property int $rate_id
 * @property int $employment_id
 * @property int $views
 * @property float $score
 * @property float $rank
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string $recruitment
 * @property string $requirements
 * @property string $email
 * @property \Coyote\User $user
 * @property \Coyote\Firm $firm
 */
class Job extends Model
{
    use SoftDeletes;
    use Searchable {
        getIndexBody as parentGetIndexBody;
    }

    const MONTH           = 1;
    const YEAR            = 2;
    const WEEK            = 3;
    const HOUR            = 4;

    /**
     * Filling each field adds points to job offer score.
     */
    const SCORE_CONFIG = [
        'job' => ['description' => 10, 'requirements' => 10, 'salary_from' => 25, 'salary_to' => 25, 'city' => 15],
        'firm' => ['name' => 15, 'logo' => 5, 'website' => 1, 'description' => 5]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'firm_id',
        'title',
        'description',
        'requirements',
        'recruitment',
        'is_remote',
        'country_id',
        'salary_from',
        'salary_to',
        'currency_id',
        'rate_id',
        'employment_id',
        'deadline_at',
        'email',
        'enable_apply'
    ];

    protected $attributes = [
        'enable_apply' => true
    ];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * Elasticsearch type mapping
     *
     * @var array
     */
    protected $mapping = [
        "id" => [
            "type" => "long"
        ],
        "locations" => [
            "type" => "nested",
            "properties" => [
                "city" => [
                    "type" => "multi_field",
                    "fields" => [
                        "city" => ["type" => "string", "index" => "analyzed", "store" => "yes"],
                        "city_original" => ["type" => "string", "analyzer" => "keyword_analyzer"]
                    ]
                ],
                "coordinates" => [
                    "type" => "geo_point"
                ]
            ]
        ],
        "title" => [
            "type" => "string",
            "analyzer" => "default_analyzer"
        ],
        "description" => [
            "type" => "string",
            "analyzer" => "default_analyzer"
        ],
        "requirements" => [
            "type" => "string",
            "analyzer" => "default_analyzer"
        ],
        "tags" => [
            "type" => "multi_field",
            "fields" => [
                "tag" => [
                    "type" => "string"
                ],
                "original" => [
                    "type" => "string",
                    "index" => "not_analyzed"
                ]
            ]
        ],
        "firm" => [
            "type" => "object",
            "properties" => [
                "name" => [
                    "type" => "multi_field",
                    "fields" => [
                        // mozliwosc szukania po nazwie firmy
                        "name" => ["type" => "string", "analyzer" => "default_analyzer"],
                        // filtrujemy firmy po tym polu
                        "name_original" => ["type" => "string", "analyzer" => "keyword_analyzer"]
                    ]
                ]
            ]
        ],
        "created_at" => [
            "type" => "date",
            "format" => "yyyy-MM-dd HH:mm:ss"
        ],
        "updated_at" => [
            "type" => "date",
            "format" => "yyyy-MM-dd HH:mm:ss"
        ],
        "deadline_at" => [
            "type" => "date",
            "format" => "yyyy-MM-dd HH:mm:ss"
        ],
        "salary" => [
            "type" => "float"
        ],
        "score" => [
            "type" => "long"
        ],
        "rank" => [
            "type" => "float"
        ]
    ];

    /**
     * We need to set firm id to null offer is private
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function (Job $model) {
            // nullable column
            foreach (['firm_id', 'salary_from', 'salary_to'] as $column) {
                if (empty($model->{$column})) {
                    $model->{$column} = null;
                }
            }

            $model->score = $model->getScore();
            $timestamp = $model->created_at ? strtotime($model->created_at) : time();

            $seconds = ($timestamp - 1380585600) / 35000;
            $model->rank = number_format($model->score + $seconds, 6, '.', '');
        });
    }

    /**
     * @return string[]
     */
    public static function getRatesList()
    {
        return [self::MONTH => 'miesięcznie', self::YEAR => 'rocznie', self::WEEK => 'tygodniowo', self::HOUR => 'godzinowo'];
    }

    /**
     * @return string[]
     */
    public static function getEmploymentList()
    {
        return [1 => 'Umowa o pracę', 2 => 'Umowa zlecenie', 3 => 'Umowa o dzieło', 4 => 'Kontrakt'];
    }

    /**
     * @return int
     */
    public function getScore()
    {
        $score = 0;

        foreach (self::SCORE_CONFIG['job'] as $column => $point) {
            if (!empty($this->{$column})) {
                $score += $point;
            }
        }

        // 30 points maximum...
        $score += min(30, (count($this->tags()->get()) * 10));

        if ($this->firm_id) {
            $firm = $this->firm;

            foreach (self::SCORE_CONFIG['firm'] as $column => $point) {
                if (!empty($firm->{$column})) {
                    $score += $point;
                }
            }

            $score += min(25, $firm->benefits()->count() * 5);
            $score -= ($firm->is_agency * 10);
        }

        return $score;
    }

    /**
     * Scope for currently active job offers
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return mixed
     */
    public function scopePriorDeadline($query)
    {
        return $query->where('deadline_at', '>', date('Y-m-d H:i:s'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function locations()
    {
        return $this->hasMany('Coyote\Job\Location');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function page()
    {
        return $this->morphOne('Coyote\Page', 'content');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function firm()
    {
        return $this->belongsTo('Coyote\Firm');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo('Coyote\Currency');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referers()
    {
        return $this->hasMany('Coyote\Job\Referer');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('Coyote\Tag', 'job_tags')->withPivot('priority');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscribers()
    {
        return $this->hasMany('Coyote\Job\Subscriber');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function applications()
    {
        return $this->hasMany('Coyote\Job\Application');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Coyote\User');
    }

    /**
     * @param string $title
     */
    public function setTitleAttribute($title)
    {
        $title = trim($title);

        $this->attributes['title'] = $title;
        $this->attributes['slug'] = str_slug($title, '_');
    }

    /**
     * @param string $value
     */
    public function setSalaryFromAttribute($value)
    {
        $this->attributes['salary_from'] = (int) trim($value);
    }

    /**
     * @param string $value
     */
    public function setSalaryToAttribute($value)
    {
        $this->attributes['salary_to'] = (int) trim($value);
    }

    /**
     * @param int $userId
     */
    public function setDefaultUserId($userId)
    {
        if (empty($this->user_id)) {
            $this->user_id = $userId;
        }
    }

    /**
     * @return array
     */
    protected function getIndexBody()
    {
        $this->setCharFilter(JobFilter::class);
        $body = $this->parentGetIndexBody();

        // maximum offered salary
        $salary = max($this->salary_from, $this->salary_to);
        $body = array_except($body, ['deleted_at', 'enable_apply']);

        // we need to calculate monthly salary in order to sorting data by salary
        if ($salary && $this->rate_id != self::MONTH) {
            if ($this->rate_id == self::YEAR) {
                $salary = round($salary / 12);
            } elseif ($this->rate_id == self::WEEK) {
                $salary = round($salary * 4);
            } else {
                $salary = round($salary * 8 * 5 * 4);
            }
        }

        $locations = [];

        // We need to transform locations to format acceptable by elasticsearch.
        // I'm talking here about the coordinates
        /** @var \Coyote\Job\Location $location */
        foreach ($this->locations()->get(['city', 'longitude', 'latitude']) as $location) {
            $nested = ['city' => $location->city];

            if ($location->latitude && $location->longitude) {
                $nested['coordinates'] = [
                    'lat' => $location->latitude,
                    'lon' => $location->longitude
                ];
            }

            $locations[] = $nested;
        }

        $body['score'] = intval($body['score']);

        $body = array_merge($body, [
            'locations'         => $locations,
            'salary'            => $salary,
            // yes, we index currency name so we don't have to look it up in database during search process
            'currency_name'     => $this->currency()->value('name'),
            'firm'              => $this->firm()->first(['name', 'logo']),
            'tags'              => $this->tags()->orderBy('priority', 'DESC')->pluck('name')
        ]);

        return $body;
    }
}
