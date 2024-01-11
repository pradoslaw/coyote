<?php
namespace Coyote\Domain\Seo;

use Coyote\Domain\Html;
use Coyote\Domain\Seo\Schema\Thing;

class Schema extends Html
{
    public function __construct(Thing $thing)
    {
        parent::__construct(
            '<script type="application/ld+json">' .
            \json_encode($thing->schema()) .
            '</script>'
        );
    }
}
