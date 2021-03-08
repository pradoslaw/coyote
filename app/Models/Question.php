<?php

namespace Coyote\Models;

use Coyote\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property User $user
 */
class Question extends Model
{
    private $excerpt = null;
    private $html = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getExcerptAttribute()
    {
        if ($this->html !== null) {
            return $this->html;
        }

        return $this->html = app('parser.post')->parse($this->text);
    }

    public function getHtmlAttribute()
    {
        if ($this->html !== null) {
            return $this->html;
        }

        return $this->html = app('parser.post')->parse($this->text);
    }
}
