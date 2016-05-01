<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Pastebin extends Model
{
    /**
     * @var string
     */
    protected $table = 'pastebin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['text', 'expired_at', 'user_name', 'syntax'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $dates = ['created_at', 'expired_at'];
}
