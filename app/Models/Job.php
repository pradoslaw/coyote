<?php

namespace Coyote;

use Coyote\Elasticsearch\Elasticsearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes, Elasticsearch;

    const MONTH            = 1;
    const YEAR            = 2;
    const WEEK            = 3;
    const HOUR            = 4;

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
        "locations" => [
            "type" => "nested",
            "properties" => [
                "city" => [
                    "type" => "multi_field",
                    "fields" => [
                        "city" => ["type" => "string", "index" => "analyzed", "store" => "yes"],
                        "city_original" => ["type" => "string", "analyzer" => "analyzer_keyword"]
                    ]
                ],
                "coordinates" => [
                    "type" => "geo_point"
                ]
            ]
        ],
        "tags" => [
            "type" => "multi_field",
            "fields" => [
                "tag" => [
                    "type" => "string"
                ],
                "tag_original" => [
                    "type" => "string",
                    "index" => "not_analyzed"
                ]
            ]
        ],
        "firm" => [
            "properties" => [
                "name" => [
                    "type" => "string",
                    "analyzer" => "analyzer_keyword"
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
        ]
    ];

    /**
     * We need to set firm id to null offer is private
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            foreach (['firm_id', 'salary_from', 'salary_to'] as $column) {
                if (empty($model->$column)) {
                    $model->$column = null;
                }
            }
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
     * Scope for currently active job offers
     *
     * @param $query
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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
     * @param string $title
     */
    public function setTitleAttribute($title)
    {
        $this->attributes['title'] = $title;
        $this->attributes['path'] = str_slug($title, '_');
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
        // maximum offered salary
        $salary = $this->salary_to;
        $body = array_except($this->toArray(), ['deleted_at', 'enable_apply']);

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

        $body = array_merge($body, [
            'locations'         => $locations,
            'salary'            => $salary,
            // yes, we index currency name so we don't have to look it up in database during search process
            'currency_name'     => $this->currency()->value('name'),
            'firm'              => $this->firm()->first(['name', 'logo']),
            'tags'              => $this->tags()->orderBy('priority', 'DESC')->pluck('name')
        ]);

        foreach (['created_at', 'updated_at', 'deadline_at'] as $column) {
            if (!empty($body[$column])) {
                $body[$column] = date('Y-m-d H:i:s', strtotime($body[$column]));
            }
        }

        return $body;
    }

    /**
     * @return string
     */
    protected function getResponseClass()
    {
        return 'Coyote\Elasticsearch\Response\Job';
    }
}
