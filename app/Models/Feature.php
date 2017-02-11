<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property string $name
 * @property string $default
 * @property Pivot $pivot
 */
class Feature extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;
}
