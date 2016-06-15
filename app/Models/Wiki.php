<?php

namespace Coyote;

use Coyote\Wiki\Page as Wiki_Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $path_id
 * @property int $parent_id
 * @property int $views
 * @property string $title
 * @property string $long_title
 * @property string $slug
 * @property string $excerpt
 * @property string $text
 * @property string $path
 * @property int $is_locked
 * @property string $template
 * @property Wiki\Comment[] $comments
 */
class Wiki extends Model
{
    use SoftDeletes;
    use Searchable {
        getIndexBody as parentGetIndexBody;
    }

    /**
     * @var string
     */
    protected $table = 'wiki';

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
     * Elasticsearch type mapping
     *
     * @var array
     */
    protected $mapping = [
        "created_at" => [
            "type" => "date",
            "format" => "yyyy-MM-dd HH:mm:ss"
        ],
        "updated_at" => [
            "type" => "date",
            "format" => "yyyy-MM-dd HH:mm:ss"
        ],
    ];

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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paths()
    {
        return $this->hasMany('Coyote\Wiki\Path');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent()
    {
        return $this->hasOne('Coyote\Wiki', 'path_id', 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany('Coyote\Wiki', 'parent_id', 'path_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('Coyote\Wiki\Comment');
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function wasUserInvolved($userId)
    {
        return $this->logs()->forUser($userId)->exists();
    }

    /**
     * @param string $column
     * @param int $amount
     * @param array $extra
     * @return mixed
     */
    public function increment($column, $amount = 1, array $extra = [])
    {
        // we cannot update view so let's update "views" column in wiki_pages table
        $page = new Wiki_Page();
        $page->timestamps = false;

        $page->where('id', $this->id)->update([$column => $this->views + $amount]);
    }

    /**
     * Default data to index in elasticsearch
     *
     * @return mixed
     */
    protected function getIndexBody()
    {
        $body = $this->parentGetIndexBody();

        return array_except($body, ['is_locked', 'templates', 'views']);
    }
}
