<?php
namespace Coyote\Models;

use Carbon\Carbon;
use Coyote\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $title
 * @property Carbon $created_at
 */
class Survey extends Model
{
    protected $fillable = ['title', 'created_at'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
