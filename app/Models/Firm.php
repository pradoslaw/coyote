<?php

namespace Coyote;

use Coyote\Services\Media\Factory as MediaFactory;
use Coyote\Services\Media\Logo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $is_agency
 * @property int $user_id
 * @property bool $is_private
 * @property string $name
 * @property string $city
 * @property string $street
 * @property string $house
 * @property string $postcode
 * @property string $website
 * @property string $description
 * @property \Coyote\Firm\Benefit[] $benefits
 * @property Logo $logo
 */
class Firm extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
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
        'longitude',
        'is_private'
    ];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * Default fields values. Important for vue.js
     *
     * @var array
     */
    protected $attributes = [
        'is_agency' => false
    ];

    /**
     * Do not change default value. It is set to FALSE on purpose.
     *
     * @var bool
     */
    protected $isPrivate = false;

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            foreach (['latitude', 'longitude', 'founded', 'employees', 'headline', 'description', 'latitude', 'longitude', 'country_id', 'street', 'city', 'house', 'postcode'] as $column) {
                if (empty($model->{$column})) {
                    $model->{$column} = null;
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

        for ($i = 1900, $year = date('Y'); $i <= $year; $i++) {
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
     * @param string $value
     * @return \Coyote\Services\Media\MediaInterface
     */
    public function getLogoAttribute($value)
    {
        if (!($value instanceof Logo)) {
            $logo = app(MediaFactory::class)->make('logo', ['file_name' => $value]);
            $this->attributes['logo'] = $logo;
        }

        return $this->attributes['logo'];
    }

    /**
     * @param bool $flag
     */
    public function setIsPrivateAttribute($flag)
    {
        $this->isPrivate = $flag;
    }

    /**
     * @return bool
     */
    public function getIsPrivateAttribute()
    {
        return $this->isPrivate;
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

    public function __sleep()
    {
        if ($this->logo instanceof Logo) {
            $this->attributes['logo'] = $this->logo->getFilename();
        }

        $properties = (new \ReflectionClass($this))->getProperties();

        $result = [];

        foreach ($properties as $property) {
            if (!$property->isStatic()) {
                $result[] = $property->getName();
            }
        }

        return $result;
    }
}
