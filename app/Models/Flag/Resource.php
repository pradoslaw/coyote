<?php

namespace Coyote\Models\Flag;

use Coyote\Flag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'flag_resources';

    public $timestamps = false;

    public function flag(): BelongsTo
    {
        return $this->belongsTo(Flag::class)->withTrashed();
    }
}
