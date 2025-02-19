<?php
namespace Coyote\Models;

use Coyote\Payment;
use Coyote\Plan;
use Coyote\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $remaining
 */
class UserPlanBundle extends Model
{
    public $timestamps = false;

    public static function boot(): void
    {
        parent::boot();
        static::creating(fn($model) => $model->created_at = $model->freshTimestamp());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
