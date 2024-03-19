<?php
namespace Neon;

class View
{
    public function __construct(readonly private string $title)
    {
    }

    public function html(): string
    {
        return <<<html
            <!DOCTYPE html>
            <html>
            <head>
              <meta charset="utf-8">
              <title>$this->title</title>
            </head>
            </html>
            html;
    }
}
