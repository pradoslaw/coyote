<?php

namespace Coyote\Services\Stream\Render;

class Person extends Render
{
    // display user's email
    public function excerpt()
    {
        if (!empty($this->stream['object.email'])) {
            return 'E-mail: ' . $this->stream['object.email'];
        }
    }
}
