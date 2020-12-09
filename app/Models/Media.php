<?php

namespace Coyote\Models;

use Coyote\Services\Media\Factory as MediaFactory;
use Coyote\Services\Media\File;
use Coyote\Services\Media\Url;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $path
 * @property Url $url
 */
class Media extends Model
{
    const UPDATED_AT = null;

    /**
     * @var string[]
     */
    protected $fillable = ['content_id', 'content_type', 'name', 'path', 'size', 'mime'];

    /**
     * @var string
     */
    protected $table = 'media';

    private ?File $file = null;

    public function getUrlAttribute()
    {
        if ($this->file === null) {
            $file = app(MediaFactory::class)->make('attachment', ['file_name' => $this->attributes['path']]);
            $this->file = $file;
        }

        return $this->file->url();
    }
}
