<?php

namespace Coyote\Microblog;

use Coyote\Microblog;
use Coyote\Models\Scopes\ForUser;
use Coyote\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $microblog_id
 * @property int $user_id
 * @property string $ip
 * @property \Coyote\User $user
 * @property \Coyote\Microblog $microblog
 */
class Vote extends Model
{
    use ForUser;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['microblog_id', 'user_id', 'ip'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'microblog_votes';

    /**
     * @var array
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function microblog()
    {
        return $this->belongsTo(Microblog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
