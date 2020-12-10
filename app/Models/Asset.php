<?php

namespace Coyote\Models;

use Coyote\Post;
use Coyote\Services\Media\Factory as MediaFactory;
use Coyote\Services\Media\File;
use Coyote\Services\Media\Url;
use Illuminate\Database\Eloquent\Model;

/**
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

    private ?File $file = null;

    public function post()
    {
        return $this->morphTo(Post::class);
    }

    public function getUrlAttribute()
    {
        if ($this->file === null) {
            $file = app(MediaFactory::class)->make('attachment', ['file_name' => $this->attributes['path']]);
            $this->file = $file;
        }

        return $this->file->url();
    }

    public function isImage()
    {
        return in_array(pathinfo($this->name, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']);
    }
}
