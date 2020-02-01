<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Twig_Extension;
use Twig_SimpleFunction;

class Elasticsearch extends Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'TwigBridge_Extension_Elasticsearch';
    }

    /**
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'elastic_highlight',
                function ($highlight, $default) {
                    return $highlight ? $highlight->implode(' ... ') : $default;
                },
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}
