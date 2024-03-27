<?php
namespace Neon\View\Language;

interface Language
{
    public function t(string $phrase): string;
}
