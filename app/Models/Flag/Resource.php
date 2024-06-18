<?php

namespace Coyote\Models\Flag;

use Coyote\Flag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Flag $flag
 */
class Resource extends Model
{
    protected $table = 'flag_resources';
    public $timestamps = false;

    public function flag(): BelongsTo
    {
        return $this->belongsTo(Flag::class)->withTrashed();
    }
}
