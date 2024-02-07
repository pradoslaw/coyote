<?php
namespace Coyote\Domain;

class Clock
{
    public function year(): int
    {
        return date('Y');
    }
}
