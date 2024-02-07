<?php
namespace Coyote\Domain;

class Clock
{
    public function year(): int
    {
        return date('Y');
    }

    public function executionTime(): float
    {
        if (defined('LARAVEL_START')) {
            return \microtime(true) - \LARAVEL_START;
        }
        return 0;
    }
}
