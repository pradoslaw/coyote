<?php
namespace Neon\View\Language;

interface Language
{
    /*
     * Translate literally
     */
    public function t(string $phrase): string;

    /*
     * Translate with declination 
     */
    public function dec(int $plurality, string $noun): string;
}
