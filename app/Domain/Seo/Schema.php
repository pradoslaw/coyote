<?php
namespace Coyote\Domain\Seo;

use Coyote\Domain\Html;
use Coyote\Domain\Seo\Schema\Thing;

class Schema extends Html
{
    public function __construct(private Thing $thing)
    {
    }

    protected function toHtml(): string
    {
        return '<script type="application/ld+json">' .
            \json_encode($this->thing->schema()) .
            '</script>';
    }
}
