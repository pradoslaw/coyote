<?php

namespace Coyote\Services\Stream\Activities;

class Throttle extends Activity
{
    /**
     * @var string
     */
    public $login;

    /**
     * @param string $login
     * @return $this
     */
    public function setLogin($login)
    {
        $this->login = $login;
        
        return $this;
    }
}
