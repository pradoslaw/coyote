<?php
namespace Neon\View;

readonly class Favicon
{
    public function __construct(private string $faviconUrl)
    {
    }

    public function html(): string
    {
        return <<<favicon
            <link rel="shortcut icon" href="$this->faviconUrl" type="image/png">
            favicon;
    }
}
