<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $length
 * @property int $votes
 * @property int $max_items
 * @property int $is_enabled
 * @property string $title
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Poll extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'length', 'votes', 'max_items'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $attributes = [
        'max_items' => 1,
        'length' => 0
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany('Coyote\Poll\Item')->orderBy('id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function votes()
    {
        return $this->hasMany('Coyote\Poll\Vote');
    }

    /**
     * @return static
     */
    public function expiredAt()
    {
        return $this->created_at->addDay($this->length);
    }

    /**
     * @return bool
     */
    public function hasExpired()
    {
        return $this->length > 0 ? Carbon::now() > $this->expiredAt() : false;
    }

    /**
     * @param int $userId
     * @return array
     */
    public function userVotedItems($userId)
    {
        return $this->votes()->forUser($userId)->pluck('item_id')->toArray();
    }
}
