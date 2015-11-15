<?php

namespace TwigBridge\Extensions;

use Carbon\Carbon;
use Jenssegers\Eloquent\Model;
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
            ),

            // w calym serwisie ciagle trzeba generowac link do profilu usera. ta funkcja umozliwia
            // generowanie linku do profilu na podstawie dostarczonych parametrow. parametrzem moze
            // byc albo tablica albo poszczegolne dane takie jak ID, login oraz informacje czy uzytkownik
            // jest zablokowany lub zbanowany
            new Twig_SimpleFunction(
                'link_to_profile',
                function () {
                    $args = func_get_args();

                    if (is_array($args[0])) {
                        $userId     = isset($args['user_id']) ? $args['user_id'] : $args['id'];
                        $name       = isset($args['user_name']) ? $args['user_name'] : $args['name'];
                        $isActive   = $args['is_active'];
                        $isBlocked  = $args['is_blocked'];
                    } else {
                        $userId     = array_shift($args);
                        $name       = array_shift($args);
                        $isActive   = array_shift($args);
                        $isBlocked  = array_shift($args);
                    }

                    $attributes     = ['data-user-id' => $userId];
                    if ($isBlocked || !$isActive) {
                        $attributes['class'] = 'del';
                    }
                    return link_to_route('profile', $name, $userId, $attributes);
                },
                ['is_safe' => ['html']]
            ),

            new Twig_SimpleFunction(
                'user_photo',
                function ($photo) {
                    return $photo ? asset($photo) : asset('img/avatar.png');
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

                if ($dateTime instanceof Carbon) {
                    $now = Carbon::now();

                    if ($dateTime->diffInHours($now) < 1) {
                        return $dateTime->diffForHumans();
                    } elseif ($dateTime->isToday()) {
                        return 'dziś, ' . $dateTime->format('H:i');
                    } elseif ($dateTime->isYesterday()) {
                        return 'wczoraj, ' . $dateTime->format('H:i');
                    } else {
                        return $dateTime->formatLocalized($format);
                    }
                } else {
                    return strftime($format, strtotime($dateTime));
                }
            }),

            new Twig_SimpleFilter('timestamp', function ($dateTime) {
                if ($dateTime instanceof Carbon) {
                    return $dateTime->getTimestamp();
                } else {
                    return strtotime($dateTime);
                }
            })
        ];
    }
}