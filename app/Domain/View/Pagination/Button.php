<?php
namespace Coyote\Domain\View\Pagination;

class Button
{
    public function __construct(
        public string $htmlValue,
        public ?int   $hrefPage,
        public string $cssClass,
    )
    {
    }

    public function isLink(): bool
    {
        return $this->hrefPage !== null;
    }
}
