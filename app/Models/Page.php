<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $content_id
 * @property string $content_type
 * @property string $path
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Page extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'path', 'allow_sitemap'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function content()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visits()
    {
        return $this->hasMany('Coyote\Page\Visit');
    }

    /**
     * @param string $path
     */
    public function setPathAttribute($path)
    {
        $this->attributes['path'] = urldecode($path);
    }
}
