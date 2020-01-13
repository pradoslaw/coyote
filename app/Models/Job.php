<?php

namespace Coyote;

use Carbon\Carbon;
use Coyote\Job\Comment;
use Coyote\Job\Location;
use Coyote\Job\Subscriber;
use Coyote\Models\Scopes\ForUser;
use Coyote\Services\Eloquent\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\RoutesNotifications;

/**
 * @property int $id
 * @property int $user_id
 * @property int $firm_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property \Carbon\Carbon $deadline_at
 * @property \Carbon\Carbon $boost_at
 * @property bool $is_expired
 * @property int $salary_from
 * @property int $salary_to
 * @property int $currency_id
 * @property int $is_remote
 * @property int $remote_range
 * @property int $enable_apply
 * @property int $visits
 * @property string $rate
 * @property bool $is_gross
 * @property string $employment
 * @property string $seniority
 * @property int $views
 * @property float $score
 * @property float $rank
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string $recruitment
 * @property string $email
 * @property string $phone
 * @property User $user
 * @property Firm $firm
 * @property Tag[] $tags
 * @property Location[] $locations
 * @property Currency[] $currency
 * @property Feature[] $features
 * @property int $plan_id
 * @property bool $is_boost
 * @property bool $is_publish
 * @property bool $is_ads
 * @property bool $is_highlight
 * @property bool $is_on_top
 * @property Plan $plan
 * @property Comment[] $comments
 */
class Job extends Model
{
    use SoftDeletes, ForUser, RoutesNotifications;
    use Searchable {
        getIndexBody as parentGetIndexBody;
    }

    const MONTHLY           = 'monthly';
    const YEARLY            = 'yearly';
    const WEEKLY            = 'weekly';
    const HOURLY            = 'hourly';

    const STUDENT           = 'student';
    const JUNIOR            = 'junior';
    const MID               = 'mid';
    const SENIOR            = 'senior';
    const LEAD              = 'lead';
    const MANAGER           = 'manager';

    const NET             = 0;
    const GROSS           = 1;

    const EMPLOYMENT      = 'employment';
    const MANDATORY       = 'mandatory';
    const CONTRACT        = 'contract';
    const B2B             = 'b2b';

    /**
     * Filling each field adds points to job offer score.
     */
    const SCORE_CONFIG = [
        'job'             => ['salary_from' => 25, 'salary_to' => 25, 'city' => 15, 'seniority' => 5],
        'firm'            => ['name' => 15, 'logo' => 5, 'website' => 1, 'description' => 5]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'recruitment',
        'is_remote',
        'is_gross',
        'remote_range',
        'salary_from',
        'salary_to',
        'currency_id',
        'rate',
        'employment',
        'deadline_at',
        'email',
        'phone',
        'enable_apply',
        'seniority',
        'plan_id',
        'tags',
        'features',
        'locations',

        'currency',
        'plan',
    ];

    /**
     * Default fields values.
     *
     * @var array
     */
    protected $attributes = [
        'enable_apply'      => true,
        'is_remote'         => false,
        'title'             => '',
        'remote_range'      => 100,
        'currency_id'       => Currency::PLN,
        'is_gross'          => self::NET,
        'rate'              => self::MONTHLY,
        'employment'        => self::EMPLOYMENT
    ];

    /**
     * Cast to when calling toArray() (for example before index in elasticsearch).
     *
     * @var array
     */
    protected $casts = [
        'is_remote'         => 'boolean',
        'is_boost'          => 'boolean',
        'is_gross'          => 'boolean',
        'is_publish'        => 'boolean',
        'is_ads'            => 'boolean',
        'is_highlight'      => 'boolean',
        'is_on_top'         => 'boolean',
        'plan_id'           => 'int',
        'score'             => 'int',
        'enable_apply'      => 'boolean'
    ];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deadline_at', 'boost_at'];

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
                    "type" => "text",
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
                ],
                "slug" => [
                    "type" => "text",
                    "analyzer" => "keyword_analyzer"
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
        "boost_at" => [
            "type" => "date",
            "format" => "yyyy-MM-dd HH:mm:ss"
        ],
        "salary" => [
            "type" => "float"
        ],
        "referral_bonus" => [
            "type" => "long"
        ],
        "score" => [
            "type" => "long"
        ],
        "is_boost" => [
            "type" => "boolean"
        ],
        "is_publish" => [
            "type" => "boolean"
        ],
        "is_ads" => [
            "type" => "boolean"
        ],
        "is_on_top" => [
            "type" => "boolean"
        ],
        "is_highlight" => [
            "type" => "boolean"
        ]
    ];

    /**
     * We need to set firm id to null offer is private
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function (Job $model) {
            $model->score = $model->getScore();

            // field must not be null
            $model->is_remote = (int) $model->is_remote;
        });

        static::creating(function (Job $model) {
            $model->boost_at = $model->freshTimestamp();
        });
    }

    /**
     * @return string[]
     */
    public static function getRatesList()
    {
        return trans('common.rate');
    }

    /**
     * @return string[]
     */
    public static function getTaxList()
    {
        return trans('common.tax');
    }

    /**
     * @return string[]
     */
    public static function getEmploymentList()
    {
        return trans('common.employment');
    }

    /**
     * @return string[]
     */
    public static function getSeniorityList()
    {
        return trans('common.seniority');
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

        // 70 points maximum...
        foreach (self::SCORE_CONFIG['job'] as $column => $point) {
            if (!empty($this->{$column})) {
                $score += $point;
            }
        }

        // 30 points maximum...
        $score += min(30, (count($this->tags()->get()) * 10));
        // 50 points maximum
        $score += min(50, count($this->features()->wherePivot('checked', true)->get()) * 5);

        if ($this->firm_id) {
            $firm = $this->firm;

            // 26 points maximum ...
            foreach (self::SCORE_CONFIG['firm'] as $column => $point) {
                if (!empty($firm->{$column})) {
                    $score += $point;
                }
            }

            // 25 points maximum...
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
        return $this->belongsToMany('Coyote\Tag', 'job_tags')->withPivot(['priority', 'order']);
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
        return $this->hasMany(Subscriber::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function applications()
    {
        return $this->hasMany('Coyote\Job\Application');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refers()
    {
        return $this->hasMany('Coyote\Job\Refer');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Coyote\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany('Coyote\Payment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commentsWithChildren()
    {
        return $this
            ->comments()
            ->whereNull('parent_id')
            ->orderBy('id', 'DESC')
            ->with([
                'children' => function ($builder) {
                    return $builder->with(['user' => function ($query) {
                        return $query->select(['id', 'name', 'photo', 'deleted_at', 'is_blocked'])->withTrashed();
                    }]);
                },
                'user' => function ($builder) {
                    return $builder->select(['id', 'name', 'photo', 'deleted_at', 'is_blocked'])->withTrashed();
                }
            ]);
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
     * @return int
     */
    public function getDeadlineAttribute()
    {
        return (new Carbon($this->deadline_at))->diff(Carbon::now(), false)->days;
    }

    /**
     * @return bool
     */
    public function getIsExpiredAttribute()
    {
        return (new Carbon($this->deadline_at))->isPast();
    }

    /**
     * @return string
     */
    public function getCurrencySymbolAttribute()
    {
        return $this->currency()->value('symbol');
    }

    /**
     * @param mixed $features
     */
    public function setFeaturesAttribute($features)
    {
        $this->features->flush();

        foreach ($features as $feature) {
            $checked = (int) $feature['checked'];

            $pivot = $this->features()->newPivot([
                'checked'       => $checked,
                'value'         => $checked ? ($feature['value'] ?? null) : null
            ]);

            $model = Feature::findOrNew($feature['id'])->setRelation('pivot', $pivot);
            $this->features->add($model);
        }
    }

    public function setTagsAttribute($tags)
    {
        $this->tags->flush();

        foreach ($tags as $tag) {
            $pivot = $this->tags()->newPivot(['priority' => $tag['priority'] ?? 1]);
            $model = (new Tag($tag))->setRelation('pivot', $pivot);

            $this->tags->add($model);
        }
    }

    public function setLocationsAttribute($locations)
    {
        $this->locations->flush();

        // remove empty locations before adding...
        foreach (array_filter(array_map('array_filter', $locations)) as $location) {
            $this->locations->add(new Job\Location($location));
        }
    }

    public function setPlanIdAttribute($value)
    {
        // set default deadline_at date time, only if offer was not publish yet.
        if (!$this->is_publish) {
            $this->attributes['plan_id'] = $value;

            $this->deadline_at = Carbon::now()->addDays($this->plan->length);
        }
    }

    public function setCurrencyAttribute($currency)
    {
        $this->attributes['currency_id'] = Currency::where('name', $currency)->value('id');
    }

    public function setRecruitmentAttribute($recruitment)
    {
        if (!empty($recruitment)) {
            $this->attributes['enable_apply'] = false;
        }

        $this->attributes['recruitment'] = $recruitment;
    }

    /**
     * Set plan as name
     *
     * @param string $plan
     */
    public function setPlanAttribute($plan)
    {
        $this->plan_id = Plan::where('is_active', 1)->whereRaw('LOWER(name) = ?', $plan)->value('id');
    }

    /**
     * @return Payment
     */
    public function getUnpaidPayment()
    {
        return $this->payments()->where('status_id', Payment::NEW)->orderBy('created_at', 'DESC')->first();
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
     * @return string
     */
    public function routeNotificationForTwilio()
    {
        return $this->phone;
    }

    /**
     * @return array
     */
    protected function getIndexBody()
    {
        $body = $this->parentGetIndexBody();

        // maximum offered salary
        $salary = $this->monthlySalary(max($this->salary_from, $this->salary_to));
        $body = array_only($body, ['id', 'slug', 'created_at', 'updated_at', 'boost_at', 'deadline_at', 'firm_id', 'is_remote']);

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
            'remote_range'      => $this->is_remote ? $this->remote_range : null
        ]);

        if ($this->firm_id) {
            // logo is instance of File object. casting to string returns file name.
            // cast to (array) if firm is empty.
            $body['firm'] = array_map('strval', (array) array_only($this->firm->toArray(), ['name', 'logo', 'slug']));
        }

        return $body;
    }

    private function stripTags($value)
    {
        // w oferach pracy, edytor tinymce nie dodaje znaku nowej linii. zamiast tego mamy <br />. zamieniamy
        // na znak nowej linii aby poprawnie zindeksowac tekst w elasticsearch. w przeciwnym przypadku
        // teks foo<br />bar po przepuszczeniu przez stripHtml() zostalby zamieniony na foobar co niepoprawnie
        // zostaloby zindeksowane jako jeden wyraz
        return strip_tags(str_replace(['<br />', '<br>'], "\n", $value));
    }

    /**
     * @param float|null $salary
     * @return float|null
     */
    public function monthlySalary($salary)
    {
        if (empty($salary) || $this->rate === self::MONTHLY) {
            return $salary;
        }

        // we need to calculate monthly salary in order to sorting data by salary
        if ($this->rate == self::YEARLY) {
            $salary = round($salary / 12);
        } elseif ($this->rate == self::WEEKLY) {
            $salary = round($salary * 4);
        } elseif ($this->rate == self::HOURLY) {
            $salary = round($salary * 8 * 5 * 4);
        }

        return $salary;
    }
}
