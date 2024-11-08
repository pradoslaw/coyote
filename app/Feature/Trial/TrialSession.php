<?php
namespace Coyote\Feature\Trial;

use Coyote\User;
use Illuminate\Database\Eloquent;

/**
 * @property string $stage
 * @property string $assortment
 * @property boolean $badge_narrow
 */
class TrialSession extends Eloquent\Model
{
    public $timestamps = false;

    public function user(): Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
