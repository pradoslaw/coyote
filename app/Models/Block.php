<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'is_enabled', 'enable_cache', 'content'];

    /**
     * @var array
     */
    protected $attributes = [
        'is_enabled' => true,
        'enable_cache' => true
    ];
}
