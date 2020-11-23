<?php

namespace Coyote;

use Coyote\Models\Scopes\ForUser;
use Coyote\Services\Media\Factory as MediaFactory;
use Coyote\Services\Media\MediaInterface;
use Coyote\Services\Media\SerializeClass;
use Illuminate\Database\Eloquent\Builder;
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
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property string $text
 * @property string $html
 * @property Microblog $parent
 * @property Microblog[] $comments
 * @property Tag[] $tags
 * @property User $user
 * @property Microblog[] $children
 * @property Microblog\Vote[]|\Illuminate\Support\Collection $voters
 */
class Microblog extends Model
{
    use SoftDeletes, Taggable, ForUser, SerializeClass;
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
     * This field does not exists in db. It's dynamically added as subquery
     *
     * @var string[]
     */
    protected $casts = ['voters_json' => 'json'];

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
        $log = $this->votes ? log((int) $this->votes, 2) : 0;

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
        if (empty($media)) {
            return;
        }

        $this->attributes['media'] = json_encode(['image' => array_values(array_filter(array_map('basename', array_pluck($media, 'url'))))]);
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
        return $this->belongsTo(User::class)->select(['id', 'name', 'deleted_at', 'is_blocked', 'photo', 'is_online'])->withTrashed();
    }

    /**
     * @param  \Illuminate\Database\Query\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Query\Builder|$this
     */
    public function scopeIncludeIsSubscribed(Builder $builder, int $userId): Builder
    {
        $this->addSelectIfNull($builder);

        return $builder
            ->addSelect(new Expression('CASE WHEN mw.user_id IS NULL THEN false ELSE true END AS is_subscribed'))
            ->leftJoin('microblog_subscribers AS mw', function ($join) use ($userId) {
                $join->on('mw.microblog_id', '=', 'microblogs.id')->where('mw.user_id', '=', $userId);
            });
    }

    public function scopeIncludeIsVoted(Builder $builder, int $userId): Builder
    {
        $this->addSelectIfNull($builder);

        return $builder
            ->addSelect(new Expression('CASE WHEN mv.id IS NULL THEN false ELSE true END AS is_voted'))
            ->leftJoin('microblog_votes AS mv', function ($join) use ($userId) {
                $join->on('mv.microblog_id', '=', 'microblogs.id')->where('mv.user_id', '=', $userId);
            });
    }

    public function scopeExclude(Builder $builder, array $exclude): Builder
    {
        if (empty($exclude)) {
            return $builder;
        }

        return $builder->whereNotIn('microblogs.user_id', $exclude);
    }

    public function scopeWithVoters(Builder $builder): Builder
    {
        $this->addSelectIfNull($builder);
        $subQuery = $builder->getQuery()->newQuery()->select('name')->from('microblog_votes')->whereRaw('microblog_id = microblogs.id')->join('users', 'users.id', '=', 'user_id');

        return $builder->selectSub(sprintf("array_to_json(array(%s))", $subQuery->toSql()), 'voters_json');
    }

    private function addSelectIfNull(Builder $builder)
    {
        if (is_null($builder->getQuery()->columns)) {
            $builder->select([$builder->getQuery()->from . '.*']);
        }
    }

    /**
     * @return MediaFactory
     */
    protected function getMediaFactory()
    {
        return app(MediaFactory::class);
    }
}
