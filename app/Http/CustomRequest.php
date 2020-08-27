<?php

namespace Coyote\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomRequest extends Request
{
    /**
     * @return mixed
     */
    public function browser()
    {
        return str_limit(Str::ascii($this->header('User-Agent')), 900);
    }
}
