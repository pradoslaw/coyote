<?php

namespace Coyote\Services\Stream\Render;

class Person extends Render
{
    // display user's email
    public function excerpt()
    {
        return 'E-mail: ' . $this->stream['object.email'];
    }
}
