<?php
namespace Coyote\Models;

use Coyote\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $title
 */
class Survey extends Model
{
    protected $fillable = ['title'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
