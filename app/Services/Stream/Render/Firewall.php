<?php

namespace Coyote\Services\Stream\Render;

class Firewall extends Render
{
    public function user()
    {
        return array_get($this->stream, 'object.model.user.name', '(brak informacji)');
    }
}
