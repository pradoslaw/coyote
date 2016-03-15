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
     * @return array
     */
    protected function getBody()
    {
        // maximum offered salary
        $salary = $this->salary_to;
        $body = array_except($this->toArray(), ['deleted_at', 'enable_apply']);

        if ($salary && $this->rate_id != self::MONTH) {
            if ($this->rate_id == self::YEAR) {
                $salary = round($salary / 12);
            } elseif ($this->rate_id == self::WEEK) {
                $salary = round($salary * 4);
            } else {
                $salary = round($salary * 8 * 5 * 4);
            }
        }

        $body = array_merge($body, [
            'locations'         => $this->locations()->lists('name'),
            'salary'            => $salary,
            // yes, we index currency name so we don't have to look it up in database during search process
            'currency_name'     => $this->currency()->pluck('name'),
            'firm'              => $this->firm()->first(['name', 'logo'])
        ]);

        return $body;
    }
}
