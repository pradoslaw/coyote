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
     * Pobiera wartosc kolumny z tabli users
     *
     * @param $name
     * @return string
     */
    public function getUserValue($name)
    {
        if (strpos($name, '.') !== false) {
            list($name, $key) = explode('.', $name);
        }

        $element = auth()->user()->$name;
        if (isset($key)) {
            $element = isset($element[$key]) ? $element[$key] : '';
        }

        return $element;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            // umozliwia szybki dostep do danych zalogowanego uzytkownika. zamiast:
            // auth()->user()->name mozemy napisac user('name')
            new Twig_SimpleFunction('user', [$this, 'getUserValue'], ['is_safe' => ['html']]),

            // robi to co funkcja powyzej z ta roznica ze najpierw sprawdza czy wartosc nie zostala
            // przekazana przez POST/GET. jezeli tak to wazniejsze sa dane przekazane w naglowku niz te
            // zapisane w bazie danych
            new Twig_SimpleFunction(
                'user_input',
                function ($name) {
                    return request($name, $this->getUserValue($name));
                }
            )
        ];
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