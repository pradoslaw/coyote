<?php

namespace Tests\Feature;

use Tests\TestCase;

class ChangeImageUrlTest extends TestCase
{
    public function testFixEmoticonsUrl()
    {
        $body = '<img src="//static.4programmers.net/img/smilies/sad.gif">';

        $message = new \Swift_Message(null, $body);
        $event = new \Illuminate\Mail\Events\MessageSending($message);

        $listener = new \Coyote\Listeners\ChangeImageUrl();
        $listener->handle($event);

        $this->assertRegExp('~<img src="https://static.4programmers.net/img/smilies/sad.gif">~', $message->getBody());
    }
}
