<?php

namespace Coyote\Services\Stream\Activities;

class ResetPassword extends Activity
{
    /**
     * @var string
     */
    public $verb = 'reset';

    /**
     * @var string
     */
    public $email;

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }
}
