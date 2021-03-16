<?php

namespace Coyote\Models;

use Coyote\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property User $user
 * @property string $title
 * @property string $excerpt
 * @property string $text
 */
class Guide extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
