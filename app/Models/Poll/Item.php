<?php

namespace Coyote\Poll;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /**
     * @var string
     */
    protected $table = 'poll_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['text', 'poll_id', 'total'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
