<?php

namespace Coyote;

use Coyote\Pm\Text;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $author_id
 * @property int $text_id
 * @property int $folder
 * @property Text $text
 * @property User $author
 * @property User $user
 * @property \Carbon\Carbon $read_at
 */
class Pm extends Model
{
    const INBOX = 1;
    const SENTBOX = 2;

    /**
     * @var string
     */
    protected $table = 'pm';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'author_id', 'text_id', 'folder'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function text()
    {
        return $this->belongsTo(Text::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
