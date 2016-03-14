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
                function ($result, $field) {
                    if (isset($result['highlight'][$field])) {
                        return $result['highlight'][$field][0];
                    } else {
                        return array_get($result['_source'], $field);
                    }
                },
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}
