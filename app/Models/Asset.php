<?php

namespace Coyote\Models;

use Coyote\Post;
use Coyote\Services\Media\Url;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $path
 * @property Url $url
 * @property Post $post
 * @property string $content_type
 * @property int $count
 */
class Asset extends Model
{
    const UPDATED_AT = null;

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'path', 'size', 'mime'];

    /**
     * @var string
     */
    protected $table = 'assets';

    public function post()
    {
        return $this->morphTo(Post::class);
    }

    public function isImage()
    {
        return in_array(pathinfo($this->name, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']);
    }
}
