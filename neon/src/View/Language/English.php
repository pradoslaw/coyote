<?php
namespace Neon\View\Language;

class English implements Language
{
    public function t(string $phrase): string
    {
        return $phrase;
    }
}
