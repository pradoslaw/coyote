<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    protected $attributes  = ['votes' => 0];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // nadajemy domyslna wartosc sortowania przy dodawaniu elementu
            $model->score = Microblog::getScore(0, (int) $model->bonus, time());
        });
    }

    /**
     * Prosty "algorytm" do generowania rankingu danego wpisu na podstawie ocen i czasu dodania
     *
     * @param $votes
     * @param $bonus
     * @param $timestamp
     * @return int
     */
    public static function getScore($votes, $bonus, $timestamp)
    {
        $log = $votes || $bonus ? log($votes + $bonus, 2) : 0;

        // magia dzieje sie tutaj :) ustalanie "mocy" danego wpisu. na tej podstawie wyswietlane
        // sa wpisy na stronie glownej. liczba glosow swiadczy o ich popularnosci
        return (int) ($log + ($timestamp / 45000));
    }

    /**
     * @param $media
     * @return mixed
     */
    public function getMediaAttribute($media)
    {
        return json_decode($media, true);
    }

    /**
     * @param $media
     */
    public function setMediaAttribute($media)
    {
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
}
