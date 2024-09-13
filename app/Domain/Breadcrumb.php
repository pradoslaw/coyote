<?php
namespace Coyote\Domain;

class Breadcrumb
{
    public function __construct(
        public string $name,
        public string $url,
        public bool   $leaf,
        public bool   $leafWithLink,
    )
    {
    }

    public function leaf(): self
    {
        return new Breadcrumb($this->name, $this->url, true, $this->leafWithLink);
    }
}
