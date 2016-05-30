<?php

namespace Coyote\Wiki;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $title
 * @property string $long_title
 * @property string $slug
 * @property string $excerpt
 * @property string $text
 * @property string $template
 * @property bool $is_locked
 */
class Page extends Model
{
    use SoftDeletes;

    const DEFAULT_TEMPLATE = 'show';

    /**
     * @var string
     */
    protected $table = 'wiki_pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'long_title', 'excerpt', 'text'];

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany('Coyote\Wiki\Log', 'wiki_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paths()
    {
        return $this->hasMany('Coyote\Wiki\Path', 'wiki_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('Coyote\Wiki\Comment', 'wiki_id', 'id');
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

    /**
     * @param array $data
     * @param bool $authorized
     */
    public function fillGuarded(array $data, $authorized)
    {
        if ($authorized) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @param \Coyote\Wiki\Path $parent
     * @param string $slug
     * @return $this
     */
    public function createPath($parent, $slug)
    {
        if (!empty($parent->path_id)) {
            $data = ['parent_id' => $parent->path_id, 'path' => $parent->path . '/' . $slug];
        } else {
            $data = ['path' => $slug];
        }

        return $this->paths()->create($data);
    }
}
