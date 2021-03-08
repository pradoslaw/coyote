<?php

namespace Coyote;

use Coyote\Microblog\Vote;
use Coyote\Models\Asset;
use Coyote\Models\Scopes\ForUser;
use Coyote\Models\Scopes\UserRelationsScope;
use Coyote\Models\Subscription;
use Coyote\Services\Media\SerializeClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Expression;

/**
 * @property int $id
 * @property int $user_id
 * @property int $parent_id
 * @property int $votes
 * @property int $score
 * @property int $is_sponsored
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
 * @property Asset[]|\Illuminate\Support\Collection $assets
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
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * @var string[]
     */
    protected $casts = ['is_sponsored' => 'bool'];

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

        static::addGlobalScope(resolve(UserRelationsScope::class));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function assets()
    {
        return $this->morphMany(Asset::class, 'content');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'resource', 'tag_resources');
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
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function subscribers()
    {
        return $this->morphMany(Subscription::class, 'resource');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function voters()
    {
        return $this->hasMany(Vote::class);
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

    public function scopeIncludeIsSubscribed(Builder $builder, int $userId): Builder
    {
        $this->addSelectIfNull($builder);

        return $builder
            ->addSelect(new Expression('CASE WHEN mw.user_id IS NULL THEN false ELSE true END AS is_subscribed'))
            ->leftJoin('subscriptions AS mw', function ($join) use ($userId) {
                $join->on('mw.resource_id', '=', 'microblogs.id')->where('mw.resource_type', '=', static::class)->where('mw.user_id', '=', $userId);
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

    private function addSelectIfNull(Builder $builder)
    {
        if (is_null($builder->getQuery()->columns)) {
            $builder->select([$builder->getQuery()->from . '.*']);
        }
    }
}
