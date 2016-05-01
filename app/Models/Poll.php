<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'length', 'votes', 'max_items', 'is_enabled'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $attributes = [
        'is_enabled' => 1,
        'max_items' => 1,
        'length' => 0
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany('Coyote\Poll\Item');
    }
}
