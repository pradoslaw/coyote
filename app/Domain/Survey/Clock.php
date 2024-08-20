<?php
namespace Coyote\Domain\Survey;

class Clock
{
    public function time(): string
    {
        return date('Y-m-d H:i:s');
    }
}
