<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $expires
 * @property string $text
 * @property string $title
 * @property string $syntax
 */
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
    protected $fillable = ['user_id', 'text', 'expires', 'title', 'syntax'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $dates = ['created_at'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
