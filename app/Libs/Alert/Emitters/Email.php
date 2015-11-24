<?php

namespace Coyote\Alert\Emitters;

use Coyote\Alert\Providers\ProviderInterface;
use Illuminate\Support\Facades\Mail;

/**
 * Class Email
 * @package Coyote\Alert\Emitters
 */
class Email extends Emitter
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $view;

    /**
     * @param $view
     * @param $email
     */
    public function __construct($view, $email)
    {
        $this->view = $view;
        $this->email = $email;
    }

    private function parse(array $data, $content)
    {
        $template = [];

        foreach ($data as $key => $value) {
            $template['{' . $key . '}'] = $value;
        }
        return str_ireplace(array_keys($template), array_values($template), $content);
    }

    /**
     * @param ProviderInterface $alert
     */
    public function send(ProviderInterface $alert)
    {
        $data = [];

        foreach (get_class_methods($alert) as $methodName) {
            if (substr($methodName, 0, 3) == 'get') {
                $reflect = new \ReflectionMethod($alert, $methodName);

                if (!$reflect->getNumberOfRequiredParameters()) {
                    $value = $alert->$methodName();

                    if (is_string($value) || is_numeric($value)) {
                        $data[snake_case(substr($methodName, 3))] = $value;
                    }
                }

                unset($reflect);
            }
        }

        $data['headline'] = $this->parse($data, $data['headline']);

        Mail::queue($this->view, $data, function ($message) use ($data) {
            $message->subject($data['headline']);
            $message->to($this->email);
            $message->from('no-reply@4programmers.net', $data['sender_name']);
        });
    }
}
