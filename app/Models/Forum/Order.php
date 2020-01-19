<?php

namespace Coyote\Forum;

use Coyote\Models\Scopes\ForUser;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use ForUser;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['forum_id', 'user_id', 'is_hidden', 'order'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'forum_orders';

    /**
     * @var array
     */
    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (empty($model->section)) {
                $model->section = null;
            }
        });
    }
}
