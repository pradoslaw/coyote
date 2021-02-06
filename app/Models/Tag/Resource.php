<?php

namespace Coyote\Tag;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $resource_id
 * @property string $resource_type
 * @property int $priority
 * @property int $order
 */
class Resource extends Model
{
    /**
     * @var string
     */
    protected $table = 'tag_resources';

    protected $fillable = ['order', 'priority', 'resource_id', 'resource_type'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
