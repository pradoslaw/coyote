<?php

namespace TwigBridge\Extensions;

use Twig_Extension;
use Twig_SimpleFunction;
use Twig_SimpleFilter;
use Illuminate\Support\Facades\Auth;

class Misc extends Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'TwigBridge_Extension_Misc';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('timer', [$this, 'getGenerationTime'])
        ];
    }

    /**
     * Zwraca czas generowania strony w sekundach lub milisekundach
     *
     * @return string
     */
    public function getGenerationTime()
    {
        // w przypadku testow funkcjonalnych, stala ta nie jest deklarowana
        if (!defined('LARAVEL_START')) {
            return false;
        }

        $timer = microtime(true) - LARAVEL_START;

        if ($timer < 1) {
            return substr((string) $timer, 2, 3) . ' ms';
        } else {
            return number_format($timer, 2) . ' s';
        }
    }
}