<?php

namespace Coyote\Wiki;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $wiki_id
 * @property int $parent_id
 * @property int $path_id
 * @property string $path
 */
class Path extends Model
{
    use SoftDeletes;

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
     * Related to Laravel 5.8. deleted_at has different date format that created_at and carbon throws exception
     *
     * @var string[]
     */
    protected $casts = ['deleted_at' => 'string'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    public $primaryKey = 'path_id';

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
