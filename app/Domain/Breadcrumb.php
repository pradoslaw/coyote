<?php
namespace Coyote\Domain;

class Breadcrumb
{
    public function __construct(
        public string $name,
        public string $url)
    {
    }
}
