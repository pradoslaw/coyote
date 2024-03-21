<?php
namespace Neon\View\Head;

readonly class Favicon implements Head
{
    public function __construct(private string $faviconUrl)
    {
    }

    public function headHtml(callable $h): string
    {
        return <<<favicon
            <link rel="shortcut icon" href="$this->faviconUrl" type="image/png">
            favicon;
    }
}
