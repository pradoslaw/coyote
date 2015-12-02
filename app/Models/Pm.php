<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

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
    protected $fillable = ['root_id', 'user_id', 'author_id', 'text_id', 'folder'];

    /**
     * @var bool
     */
    public $timestamps = false;

    public function text()
    {
//        return $this->belongsTo('Coyote\Pm\Text', 'id', 'text_id');
        return $this->belongsTo('Coyote\Pm\Text');
    }
}
