<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flag extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type_id', 'user_id', 'url', 'metadata', 'text', 'moderator_id'];

    /**
     * @var bool
     */
    public $timestamps = false;

    public function type()
    {
        return $this->hasMany('Coyote\Flag\Type');
    }
}
