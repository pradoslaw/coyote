<?php

namespace Tests\Legacy\Listeners;

use Coyote\Listeners\ChangeImageUrl;
use Illuminate\Mail\Events\MessageSending;
use Tests\TestCase;

class ChangeImageUrlTest extends TestCase
{
    public function testFixEmoticonsUrl()
    {
        // given
        $message = new \Swift_Message(null, '<img src="//static.4programmers.net/img/smilies/sad.gif">');
        // when
        $listener = new ChangeImageUrl();
        $listener->handle(new MessageSending($message));
        // then
        $this->assertSame('<img src="https://static.4programmers.net/img/smilies/sad.gif">', $message->getBody());
    }
}
