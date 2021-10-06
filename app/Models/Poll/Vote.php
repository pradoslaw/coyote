<?php

namespace Coyote\Poll;

use Coyote\Models\Scopes\ForUser;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use ForUser;

    /**
     * @var string
     */
    protected $table = 'poll_votes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'poll_id', 'ip', 'item_id', 'fingerprint'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
