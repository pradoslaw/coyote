<?php

namespace Coyote\Guide;

use Coyote\Guide;
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
    protected $fillable = ['guide_id', 'user_id', 'ip'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'guide_votes';

    /**
     * @var array
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function guide()
    {
        return $this->belongsTo(Guide::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
