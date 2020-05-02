<?php

namespace Coyote;

use Coyote\Services\Media\Factory as MediaFactory;
use Coyote\Services\Media\MediaInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Expression;

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
 * @property string $text
 * @property string $html
 * @property Microblog $parent
 * @property Microblog[] $comments
 * @property Tag[] $tags
 * @property User $user
 * @property Microblog[] $children
 */
class Microblog extends Model
{
    use SoftDeletes, Taggable;
    use Searchable{
        getIndexBody as parentGetIndexBody;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'user_id', 'text', 'media'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Domyslne wartosci dla nowego modelu
     *
     * @var array
     */
    protected $attributes = ['votes' => 0];

    /**
     * Html version of the entry.
     *
     * @var null|string
     */
    private $html = null;

    public static function boot()
    {
        parent::boot();

        static::creating(function (Microblog $model) {
            // nadajemy domyslna wartosc sortowania przy dodawaniu elementu
            $model->score = $model->getScore();
        });

        static::deleted(function (Microblog $model) {
            $model->media = null; // MUST remove closure before serializing object
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
                $media[] = $factory->make('attachment', [
                    'file_name' => $image
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
            $media = json_encode(['image' => $media]);
        }

        $this->attributes['media'] = $media;
    }

    public function setHtmlAttribute($value)
    {
        $this->html = $value;
    }

    /**
     * @return null|string
     */
    public function getHtmlAttribute()
    {
        if ($this->html !== null) {
            return $this->html;
        }

        return $this->html = app('parser.microblog')->parse($this->text);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->orderBy('microblogs.id', 'ASC');
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent()
    {
        return $this->hasOne('Coyote\Microblog', 'id', 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'deleted_at', 'is_blocked', 'photo'])->withTrashed();
    }

    /**
     * @param  \Illuminate\Database\Query\Builder $query
     * @param int|null $userId
     * @return bool|\Illuminate\Database\Query\Builder
     */
    public function scopeIncludeSubscribers($query, ?int $userId)
    {
        if (empty($userId)) {
            return false;
        }

        return $query
            ->addSelect(new Expression('CASE WHEN mw.user_id IS NULL THEN false ELSE true END AS is_subscribed'))
            ->leftJoin('microblog_subscribers AS mw', function ($join) use ($userId) {
                $join->on('mw.microblog_id', '=', 'microblogs.id')->where('mw.user_id', '=', $userId);
            });
    }

    /**
     * @param  \Illuminate\Database\Query\Builder $query
     * @param int|null $userId
     * @return bool|\Illuminate\Database\Query\Builder
     */
    public function scopeIncludeVoters($query, ?int $userId)
    {
        if (empty($userId)) {
            return false;
        }

        return $query
            ->addSelect(new Expression('CASE WHEN mv.id IS NULL THEN false ELSE true END AS is_voted'))
            ->leftJoin('microblog_votes AS mv', function ($join) use ($userId) {
                $join->on('mv.microblog_id', '=', 'microblogs.id')->where('mv.user_id', '=', $userId);
            });
    }

    /**
     * @return MediaFactory
     */
    protected function getMediaFactory()
    {
        return app(MediaFactory::class);
    }
}
