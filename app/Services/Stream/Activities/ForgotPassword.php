<?php

namespace Coyote\Services\Stream\Activities;

class ForgotPassword extends ResetPassword
{
    /**
     * @var string
     */
    public $verb = 'forgot';
}
