<?php
namespace Coyote\Domain;

class StringHtml extends Html
{
    public function __construct(private string $html)
    {
    }

    protected function toHtml(): string
    {
        return $this->html;
    }
}
