<?php

namespace TwigBridge\Extensions;

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
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'elastic_highlight',
                function ($highlight, $default) {
                    return $highlight ? implode('...', $highlight) : $default;
                },
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}
