<?php

namespace Coyote;

use Coyote\Services\Media\Factories\AbstractFactory;
use Coyote\Services\Media\MediaInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property MediaInterface[] $media
 * @property int $id
 * @property int $user_id
 * @property int $parent_id
 * @property int $votes
 * @property int $score
 * @property int $is_sponsored
 * @property int $bonus
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class Microblog extends Model
{
    use SoftDeletes, Taggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'user_id', 'text'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Domyslne wartosci dla nowego modelu
     *
     * @var array
     */
    protected $attributes = ['votes' => 0];

    public static function boot()
    {
        parent::boot();

        /**
         * @var $this $model
         */
        static::creating(function ($model) {
            // nadajemy domyslna wartosc sortowania przy dodawaniu elementu
            $model->score = $model->getScore();
        });
    }

    /**
     * Prosty "algorytm" do generowania rankingu danego wpisu na podstawie ocen i czasu dodania
     *
     * @return int
     */
    public function getScore()
    {
        $timestamp = $this->created_at ? strtotime($this->created_at) : time();
        $log = ($this->votes || $this->bonus) ? log((int) $this->votes + (int) $this->bonus, 2) : 0;

        // magia dzieje sie tutaj :) ustalanie "mocy" danego wpisu. na tej podstawie wyswietlane
        // sa wpisy na stronie glownej. liczba glosow swiadczy o ich popularnosci
        return (int) ($log + ($timestamp / 45000));
    }

    /**
     * @param string $value
     * @return mixed
     */
    public function getMediaAttribute($value)
    {
        $json = json_decode($value, true);
        $media = [];

        if (!empty($json['image'])) {
            $factory = $this->getMediaFactory();

            foreach ($json['image'] as $image) {
                $media[] = $factory->make([
                    'file_name' => $image,
                ]);
            }
        }

        return $media;
    }

    /**
     * @param $media
     */
    public function setMediaAttribute($media)
    {
        if (!empty($media)) {
            $media = ['image' => $media];
        }

        $this->attributes['media'] = json_encode($media);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('Coyote\Microblog', 'parent_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscribers()
    {
        return $this->hasMany('Coyote\Microblog\Subscriber', 'microblog_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('Coyote\Tag', 'microblog_tags');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function voters()
    {
        return $this->hasMany('Coyote\Microblog\Vote');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function page()
    {
        return $this->morphOne('Coyote\Page', 'content');
    }

    /**
     * @return AbstractFactory
     */
    protected function getMediaFactory()
    {
        return app('media.attachment');
    }
}
