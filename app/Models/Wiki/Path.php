<?php

namespace Coyote\Wiki;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $wiki_id
 * @property int $parent_id
 * @property string $path
 */
class Path extends Model
{
    /**
     * @var string
     */
    protected $table = 'wiki_paths';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['wiki_id', 'parent_id', 'path'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var bool
     */
    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            /** @var \Coyote\Wiki\Path $model */

            if (empty($model->parent_id)) {
                $model->parent_id = null;
            }
        });
    }
}
