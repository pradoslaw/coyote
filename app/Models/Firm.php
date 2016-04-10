<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Firm extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'logo',
        'website',
        'headline',
        'description',
        'employees',
        'founded',
        'is_agency',
        'country_id',
        'city',
        'street',
        'house',
        'postcode',
        'latitude',
        'longitude'
    ];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            foreach (['latitude', 'longitude', 'founded', 'employees'] as $column) {
                if (empty($model->$column)) {
                    $model->$column = null;
                }
            }
        });
    }

    /**
     * @return array
     */
    public static function getEmployeesList()
    {
        return [
            1 => '1-5',
            2 => '6-10',
            3 => '11-20',
            4 => '21-30',
            5 => '31-50',
            6 => '51-100',
            7 => '101-200',
            8 => '201-500',
            9 => '501-1000',
            10 => '1001-5000',
            11 => '5000+'
        ];
    }

    /**
     * @return array
     */
    public static function getFoundedList()
    {
        $result = [];

        for ($i = 1900; $i <= date('Y'); $i++) {
            $result[$i] = $i;
        }

        return $result;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function benefits()
    {
        return $this->hasMany('Coyote\Firm\Benefit');
    }

    /**
     * @param string $name
     */
    public function setNameAttribute($name)
    {
        $name = trim($name);

        $this->attributes['name'] = $name;
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
}
