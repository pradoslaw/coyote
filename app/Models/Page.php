<?php

namespace Coyote;

use Coyote\Page\Stat;
use Coyote\Page\Visit;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $content_id
 * @property string $content_type
 * @property string $path
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property mixed $tags
 */
class Page extends Model
{
    use Searchable{
        getIndexBody as parentGetIndexBody;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'path', 'allow_sitemap', 'tags'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $casts = ['tags' => 'json'];

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
        return $this->hasMany(Visit::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stats()
    {
        return $this->hasMany(Stat::class);
    }

    /**
     * Return data to index in elasticsearch
     *
     * @return array
     */
    protected function getIndexBody()
    {
        $this->dates = ['created_at', 'updated_at'];

        $body = $this->parentGetIndexBody();
        $body['suggest'] = $body['title'];

        return $body;

//        // we need to index every field from topics except:
//        $body = array_only($body, ['id', 'forum_id', 'subject', 'slug', 'updated_at']);
//
//
//        return array_merge($body, [
//            'posts'     => $posts,
//            'subject'   => htmlspecialchars($this->subject),
//            'forum'     => $this->forum->only(['name', 'slug'])
//        ]);
    }
}
