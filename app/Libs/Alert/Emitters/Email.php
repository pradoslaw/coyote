<?php

namespace Coyote\Alert\Emitters;

use Coyote\Alert\Providers\ProviderInterface;
use Illuminate\Support\Facades\Mail;

class Email extends Emitter
{
    /**
     * @param Mail $mail
     */
    public function __construct(Mail $mail)
    {
        //
    }

    /**
     * @param ProviderInterface $alert
     */
    public function send(ProviderInterface $alert)
    {
        $template = [];

        foreach (get_class_methods($this) as $methodName) {
            if (substr($methodName, 0, 3) == 'get') {
                $reflect = new \ReflectionMethod($this, $methodName);

                if (!$reflect->getNumberOfRequiredParameters()) {
                    $value = $this->$methodName();

                    if (is_string($value) || is_numeric($value)) {
                        $template[(strtolower(substr($methodName, 3)))] = $value;
                    }
                }

                unset($reflect);
            }
        }
    }
}
