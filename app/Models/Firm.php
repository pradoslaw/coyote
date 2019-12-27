<?php

namespace Coyote;

use Coyote\Firm\Gallery;
use Coyote\Services\Eloquent\HasMany;
use Coyote\Services\Media\Factory as MediaFactory;
use Coyote\Services\Media\Logo;
use Coyote\Services\Media\SerializeClass;
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
 * @property string $street_number
 * @property string $postcode
 * @property string $website
 * @property string $description
 * @property string $vat_id
 * @property \Coyote\Firm\Benefit[] $benefits
 * @property \Coyote\Firm\Industry[] $industries
 * @property \Coyote\Firm\Gallery[] $gallery
 * @property Logo $logo
 */
class Firm extends Model
{
    use SoftDeletes, SerializeClass;

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
        'street_number',
        'postcode',
        'latitude',
        'longitude',
        'is_private',
        'youtube_url',
        'industries',
        'gallery',
        'benefits',
        'country'
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

    protected $casts = [
        'is_agency' => 'bool',
        'is_private' => 'bool'
    ];

    protected $appends = [
        'is_private'
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
            foreach (['latitude', 'longitude', 'founded', 'employees', 'description', 'latitude', 'longitude', 'country_id', 'street', 'city', 'street_number', 'postcode', 'youtube_url'] as $column) {
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
     * @return HasMany
     */
    public function benefits()
    {
        $instance = new Firm\Benefit();

        return new HasMany($instance->newQuery(), $this, $instance->getTable() . '.' . $this->getForeignKey(), $this->getKeyName());
    }

    /**
     * @return HasMany
     */
    public function gallery()
    {
        $instance = new Firm\Gallery();

        return new HasMany($instance->newQuery(), $this, $instance->getTable() . '.' . $this->getForeignKey(), $this->getKeyName());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function photos()
    {
        return $this->hasMany(Gallery::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function industries()
    {
        return $this->belongsToMany(Industry::class, 'firm_industries');
    }

    /**
     * @param string $name
     */
    public function setNameAttribute($name)
    {
        $name = trim($name);

        $this->attributes['name'] = $name;
        $this->attributes['slug'] = str_slug($name, '_');
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
        $this->isPrivate = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getIsPrivateAttribute()
    {
        return $this->isPrivate;
    }

    public function setYoutubeUrlAttribute($value)
    {
        $this->attributes['youtube_url'] = $this->getEmbedUrl($value);
    }

    public function setBenefitsAttribute($benefits)
    {
        $benefits = array_filter(array_unique(array_map('trim', $benefits)));

        $models = [];

        foreach ($benefits as $benefit) {
            $models[] = new Firm\Benefit(['name' => $benefit]);
        }

        // call macro and replace collection items
        $this->benefits->replace($models);
    }

    public function setGalleryAttribute($gallery)
    {
        $models = [];

        foreach ($gallery as $photo) {
            if (!empty($photo)) {
                $models[] = new Firm\Gallery(['file' => $photo]);
            }
        }

        // call macro and replace collection items
        $this->gallery->replace($models);
    }

    public function setIndustriesAttribute($industries)
    {
        $models = [];

        foreach ((array) $industries as $industry) {
            $models[] = new Industry(['id' => $industry]);
        }

        $this->industries->replace($models);
    }

    /**
     * @param string $country
     */
    public function setCountryAttribute($country)
    {
        if ($country) {
            $this->setAttribute('country_id', (new Country())->where('name', $country)->orWhere('code', $country)->value('id'));
        }
    }

    /**
     * @param array $attributes
     * @return $this|Model
     */
    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if ($this->is_agency) {
            foreach (['headline', 'latitude', 'longitude', 'country_id', 'street', 'city', 'street_number', 'postcode'] as $column) {
                $this->{$column} = null;
            }

            $this->benefits->flush();
        }

        return $this;
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
     * @param string $url
     * @return string
     */
    private function getEmbedUrl($url)
    {
        if (empty($url)) {
            return '';
        }

        $components = parse_url($url);

        if (empty($components['query'])) {
            return $url;
        }

        parse_str($components['query'], $query);

        return 'https://www.youtube.com/embed/' . $query['v'];
    }
}
