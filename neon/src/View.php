<?php
namespace Neon;

readonly class View
{
    public function __construct(
        private string $applicationName,
        private string $sectionTitle,
    )
    {
    }

    public function html(): string
    {
        return <<<html
            <!DOCTYPE html>
            <html>
            <head>
              <meta charset="utf-8">
              <title>$this->applicationName</title>
            </head>
            <body>
              <nav>
                <ul>
                  <li>$this->applicationName</li>
                  <li>Events</li>
                </ul>
              </nav>
              <h1>$this->sectionTitle</h1>
            </body>
            </html>
            html;
    }
}
