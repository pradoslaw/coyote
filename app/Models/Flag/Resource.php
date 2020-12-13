<?php

namespace Coyote\Models\Flag;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'flag_resources';

    public $timestamps = false;
}
