<?php

namespace Coyote\Post;

use Coyote\Post;
use Coyote\Services\Media\File;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated
 * @property int $id
 * @property string $name
 * @property string $mime
 * @property int $size
 * @property int $count
 * @property int $post_id
 * @property File $file
 * @property Post $post
 */
class Attachment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['post_id', 'name', 'file', 'mime', 'size'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'post_attachments';
}
