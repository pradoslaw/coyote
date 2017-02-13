<?php

namespace Coyote;

use Carbon\Carbon;
use Coyote\Job\Location;
use Coyote\Models\Scopes\ForUser;
use Coyote\Services\Elasticsearch\CharFilters\JobFilter;
use Coyote\Services\Eloquent\HasMany;
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
 * @property int $deadline
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
 * @property User $user
 * @property Firm $firm
 * @property Tag[] $tags
 * @property Location[] $locations
 * @property Currency[] $currency
 * @property Feature[] $features
 */
class Job extends Model
{
    use SoftDeletes, ForUser;
    use Searchable {
        getIndexBody as parentGetIndexBody;
    }

    const MONTH           = 1;
    const YEAR            = 2;
    const WEEK            = 3;
    const HOUR            = 4;

    const STUDENT         = 1;
    const JUNIOR          = 2;
    const MID             = 3;
    const SENIOR          = 4;
    const LEAD            = 5;
    const MANAGER         = 6;

    /**
     * Filling each field adds points to job offer score.
     */
    const SCORE_CONFIG = [
        'job' => ['description' => 10, 'salary_from' => 25, 'salary_to' => 25, 'city' => 15, 'seniority_id' => 5],
        'firm' => ['name' => 15, 'logo' => 5, 'website' => 1, 'description' => 5]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'requirements',
        'recruitment',
        'is_remote',
        'remote_range',
        'country_id',
        'salary_from',
        'salary_to',
        'currency_id',
        'rate_id',
        'employment_id',
        'deadline_at',
        'email',
        'enable_apply',
        'seniority_id'
    ];

    /**
     * Default fields values.
     *
     * @var array
     */
    protected $attributes = [
        'enable_apply' => true,
        'is_remote' => false,
        'title' => ''
    ];

    /**
     * Cast to when calling toArray() (for example before index in elasticsearch).
     *
     * @var array
     */
    protected $casts = ['is_remote' => 'boolean', 'enable_apply' => 'boolean'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $appends = ['deadline'];

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
                    "type" => "string",
                    "analyzer" => "keyword_asciifolding_analyzer",
                    "fields" => [
                        "original" => ["type" => "text", "analyzer" => "keyword_analyzer", "fielddata" => true]
                    ]
                ],
                "coordinates" => [
                    "type" => "geo_point"
                ]
            ]
        ],
        "title" => [
            "type" => "text",
            "analyzer" => "default_analyzer"
        ],
        "description" => [
            "type" => "text",
            "analyzer" => "default_analyzer"
        ],
        "requirements" => [
            "type" => "text",
            "analyzer" => "default_analyzer"
        ],
        "is_remote" => [
            "type" => "boolean"
        ],
        "remote_range" => [
            "type" => "integer"
        ],
        "tags" => [
            "type" => "text",
            "fields" => [
                "original" => ["type" => "keyword"]
            ]
        ],
        "firm" => [
            "type" => "object",
            "properties" => [
                "name" => [
                    "type" => "text",
                    "analyzer" => "default_analyzer",
                    "fields" => [
                        // filtrujemy firmy po tym polu
                        "original" => ["type" => "text", "analyzer" => "keyword_analyzer", "fielddata" => true]
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
            foreach (['firm_id', 'salary_from', 'salary_to', 'remote_range', 'seniority_id'] as $column) {
                if (empty($model->{$column})) {
                    $model->{$column} = null;
                }
            }

            $model->score = $model->getScore();
            $timestamp = $model->created_at ? strtotime($model->created_at) : time();

            $seconds = ($timestamp - 1380585600) / 35000;
            $model->rank = number_format($model->score + $seconds, 6, '.', '');

            // field must not be null
            $model->is_remote = (int) $model->is_remote;
        });
    }

    /**
     * @return array
     */
    public static function getRatesList()
    {
        return [self::MONTH => 'miesięcznie', self::YEAR => 'rocznie', self::WEEK => 'tygodniowo', self::HOUR => 'godzinowo'];
    }

    /**
     * @return array
     */
    public static function getEmploymentList()
    {
        return [1 => 'Umowa o pracę', 2 => 'Umowa zlecenie', 3 => 'Umowa o dzieło', 4 => 'Kontrakt'];
    }

    /**
     * @return array[]
     */
    public static function getSeniorityList()
    {
        return [self::STUDENT => 'Stażysta', self::JUNIOR => 'Junior', self::MID => 'Mid-Level', self::SENIOR => 'Senior', self::LEAD => 'Lead', self::MANAGER => 'Manager'];
    }

    /**
     * @return array
     */
    public static function getRemoteRangeList()
    {
        $list = [];

        for ($i = 100; $i > 0; $i -= 10) {
            $list[$i] = "$i%";
        }

        return $list;
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
        $score += count($this->features()->get());

        if ($this->firm_id) {
            $firm = $this->firm;

            foreach (self::SCORE_CONFIG['firm'] as $column => $point) {
                if (!empty($firm->{$column})) {
                    $score += $point;
                }
            }

            $score += min(25, $firm->benefits()->count() * 5);
            $score -= ($firm->is_agency * 15);
        } else {
            $score -= 15;
        }

        return max(1, $score); // score can't be negative. 1 point min for elasticsearch algorithm
    }

    /**
     * Scope for currently active job offers
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopePriorDeadline($query)
    {
        return $query->where('deadline_at', '>', date('Y-m-d H:i:s'));
    }

    /**
     * @return HasMany
     */
    public function locations()
    {
        $instance = new Job\Location();

        return new HasMany($instance->newQuery(), $this, $instance->getTable() . '.' . $this->getForeignKey(), $this->getKeyName());
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
        return $this->belongsToMany('Coyote\Tag', 'job_tags')->orderBy('order')->withPivot(['priority', 'order']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function features()
    {
        return $this->belongsToMany('Coyote\Feature', 'job_features')->orderBy('order')->withPivot(['checked', 'value']);
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo('Coyote\Country');
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
        $this->attributes['salary_from'] = $value === null ? null : (int) trim($value);
    }

    /**
     * @param string $value
     */
    public function setSalaryToAttribute($value)
    {
        $this->attributes['salary_to'] = $value === null ? null : (int) trim($value);
    }

    /**
     * @param int $value
     */
    public function setDeadlineAttribute($value)
    {
        $this->attributes['deadline_at'] = Carbon::now()->addDay($value);
    }

    /**
     * @return int
     */
    public function getDeadlineAttribute()
    {
        return $this->deadline_at ? (new Carbon($this->deadline_at))->diff(Carbon::now())->days + 1 : 90;
    }

    /**
     * @return mixed
     */
    public function getCityAttribute()
    {
        return $this->locations->implode('city', ', ');
    }

    /**
     * @return string
     */
    public function getCurrencyNameAttribute()
    {
        return $this->currency()->value('name');
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
     * @param mixed $features
     */
    public function setDefaultFeatures($features)
    {
        if (!count($this->features)) {
            foreach ($features as $feature) {
                $pivot = $this->features()->newPivot(['checked' => $feature->checked, 'value' => $feature->value]);
                $this->features->add($feature->setRelation('pivot', $pivot));
            }
        }
    }

    /**
     * @param string $url
     */
    public function addReferer($url)
    {
        if ($url && mb_strlen($url) < 200) {
            $referer = $this->referers()->firstOrNew(['url' => $url]);

            if (!$referer->id) {
                $referer->save();
            } else {
                $referer->increment('count');
            }
        }
    }

    /**
     * Check if user has applied for this job offer.
     *
     * @param int|null $userId
     * @param string $sessionId
     * @return boolean
     */
    public function hasApplied($userId, $sessionId)
    {
        if ($userId) {
            return $this->applications()->forUser($userId)->exists();
        }

        return $this->applications()->where('session_id', $sessionId)->exists();
    }

    /**
     * @return array
     */
    protected function getIndexBody()
    {
        $this->setCharFilter(JobFilter::class);
        $body = $this->parentGetIndexBody();

        // maximum offered salary
        $salary = $this->monthlySalary(max($this->salary_from, $this->salary_to));
        $body = array_except($body, ['deleted_at', 'enable_apply']);

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
            'salary_from'       => $this->monthlySalary($this->salary_from),
            'salary_to'         => $this->monthlySalary($this->salary_to),
            // yes, we index currency name so we don't have to look it up in database during search process
            'currency_name'     => $this->currency()->value('name'),
            // higher tag's priorities first
            'tags'              => $this->tags()->get(['name', 'priority'])->sortByDesc('pivot.priority')->pluck('name')
        ]);

        if (!empty($body['firm'])) {
            // logo is instance of File object. casting to string returns file name.
            // cast to (array) if firm is empty.
            $body['firm'] = array_map('strval', (array) array_only($body['firm'], ['name', 'logo']));
        }

        return $body;
    }

    /**
     * @param float|null $salary
     * @return float|null
     */
    private function monthlySalary($salary)
    {
        if (empty($salary) || $this->rate_id === self::MONTH) {
            return $salary;
        }

        // we need to calculate monthly salary in order to sorting data by salary
        if ($this->rate_id == self::YEAR) {
            $salary = round($salary / 12);
        } elseif ($this->rate_id == self::WEEK) {
            $salary = round($salary * 4);
        } elseif ($this->rate_id == self::HOUR) {
            $salary = round($salary * 8 * 5 * 4);
        }

        return $salary;
    }
}
