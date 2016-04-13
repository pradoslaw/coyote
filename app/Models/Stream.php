<?php

namespace Coyote;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Stream extends Eloquent
{
    const UPDATED_AT = null;

    protected $connection = 'mongodb';
    protected $guarded = ['_id'];
}
