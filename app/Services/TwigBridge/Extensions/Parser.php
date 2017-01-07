<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Twig_Extension;
use Twig_SimpleFilter;

class Parser extends Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'TwigBridge_Extension_Parser';
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('parse', function ($text, $name) {
                return app('parser.' . $name)->parse($text);
            }),
        ];
    }
}
