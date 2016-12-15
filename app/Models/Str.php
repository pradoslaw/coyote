<?php

namespace Coyote\Models;

use Illuminate\Database\Query\Expression;

class Str extends Expression
{
    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        parent::__construct("'" . addslashes($value) . "'");
    }
}
