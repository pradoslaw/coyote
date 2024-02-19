<?php
namespace Coyote\Domain;

readonly class Url
{
    public bool $malformed;
    public ?string $host;
    public ?string $path;

    public function __construct(string $url)
    {
        $components = \parse_url($url);
        $this->malformed = $components === false;
        $this->host = $components['host'] ?? null;
        $this->path = $components['path'] ?? null;
    }
}
