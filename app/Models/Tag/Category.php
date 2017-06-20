<?php

namespace Coyote\Tag;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 */
class Category extends Model
{
    const LANGUAGE = 1;
    const DATABASE = 2;
    const FRAMEWORK = 3;

    /**
     * @var string
     */
    protected $table = 'tag_categories';

    /**
     * @var bool
     */
    public $timestamps = false;
}
