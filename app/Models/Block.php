<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $region
 * @property bool $is_enabled
 * @property string $content
 * @property int $max_reputation
 * @property bool $enable_sponsor
 */
class Block extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'region', 'is_enabled', 'content', 'max_reputation', 'enable_sponsor'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $attributes = [
        'is_enabled' => true,
        'enable_sponsor' => true
    ];
}
