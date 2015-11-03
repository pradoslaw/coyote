<?php

namespace TwigBridge\Extensions;

use Twig_Extension;
use Twig_SimpleFunction;
use Twig_SimpleFilter;
use Illuminate\Support\Facades\Auth;

class User extends Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'TwigBridge_Extension_User';
    }

    /**
     * Dodatkowe filtry Twig zwiazane z formatowaniem danych uzytkownika
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('format_date', function ($dateTime) {
                $format = Auth::check() ? auth()->user()->date_format : '%Y-%m-%d %H:%M';

                return strftime($format, strtotime($dateTime));
            })
        ];
    }
}