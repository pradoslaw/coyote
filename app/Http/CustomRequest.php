<?php

namespace Coyote\Http;

use Illuminate\Http\Request;

class CustomRequest extends Request
{
    /**
     * @return mixed
     * @todo mozna usunac? trzeba filtrowac te wartosc czy robi to symfony?
     */
    public function browser()
    {
        return filter_var($this->header('User-Agent'), FILTER_SANITIZE_STRING);
    }
}
