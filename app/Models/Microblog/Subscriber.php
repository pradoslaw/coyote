<?php

namespace Coyote\Microblog;

use Coyote\Models\Scopes\ExcludeBlocked;
use Coyote\Models\Scopes\ForUser;
use Coyote\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property User $user
 */
class Subscriber extends Model
{
    use ForUser, ExcludeBlocked;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['microblog_id', 'user_id'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'microblog_subscribers';

    /**
     * @var array
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
