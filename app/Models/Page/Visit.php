<?php

namespace Coyote\Page;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $visits
 * @property int $user_id
 */
class Visit extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'visits'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'page_visits';
}
