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
 */
class Block extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'region', 'is_enabled', 'content', 'max_reputation'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $attributes = [
        'is_enabled' => true
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function (Block $model) {
            if (!$model->max_reputation) {
                $model->max_reputation = null;
            }
        });
    }
}
