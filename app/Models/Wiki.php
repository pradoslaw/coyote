<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $parent_id
 * @property string $title
 * @property string $long_title
 * @property string $slug
 * @property string $path
 * @property string $excerpt
 * @property string $text
 * @property int $is_locked
 * @property string $template
 */
class Wiki extends Model
{
    use SoftDeletes;

    const DEFAULT_TEMPLATE = 'show';

    /**
     * @var string
     */
    protected $table = 'wiki';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'title', 'long_title', 'excerpt', 'text'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $attributes = [
        'template' => self::DEFAULT_TEMPLATE
    ];

    /**
     * @var array
     */
    protected $casts = [
        'is_locked' => 'bool'
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            /** @var \Coyote\Wiki $model */
            $model->path = $model->slug;

            if ($model->parent_id) {
                $model->path = $model->parent()->value('path') . '/' . $model->path;
            } else {
                $model->parent_id = null;
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function page()
    {
        return $this->morphOne('Coyote\Page', 'content');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscribers()
    {
        return $this->hasMany('Coyote\Wiki\Subscriber');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany('Coyote\Wiki\Log');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent()
    {
        return $this->hasOne('Coyote\Wiki', 'id', 'parent_id');
    }

    /**
     * @param string $title
     */
    public function setTitleAttribute($title)
    {
        $this->attributes['title'] = ucfirst($title);
        // ucfirst() tylko dla zachowania kompatybilnosci wstecz
        $this->attributes['slug'] = ucfirst(str_slug($title, '_'));
    }
}
