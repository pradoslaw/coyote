<?php

namespace Coyote\Models;

use Coyote\Services\Media\Url;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $path
 * @property string $mime
 * @property Url $url
 * @property string $content_type
 * @property int $count
 * @property mixed $content
 */
class Asset extends Model
{
    const UPDATED_AT = null;

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'path', 'size', 'mime', 'metadata'];

    protected $casts = ['metadata' => 'json'];

    /**
     * @var string
     */
    protected $table = 'assets';

    public function content()
    {
        return $this->morphTo();
    }

    public function isImage()
    {
        return in_array(strtolower(pathinfo($this->name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }
}
