<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $email
 */
class Mailing extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'mailing';

    /**
     * @var bool
     */
    public $incrementing = false;
}
