<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Http\Factories\GateFactory;
use Twig_Extension;
use Twig_SimpleFunction;

class User extends Twig_Extension
{
    use GateFactory;

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
        if (auth()->guest()) {
            return null;
        }

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
     * @return Twig_SimpleFunction[]
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
                function (... $args) {
                    $user = $args[0];

                    if ($user instanceof \Coyote\User) {
                        $user = $user->toArray();
                    }

                    if (is_array($user)) {
                        $userId     = isset($user['user_id']) ? $user['user_id'] : $user['id'];
                        $name       = isset($user['user_name']) ? $user['user_name'] : $user['name'];
                        $isActive   = $user['is_active'];
                        $isBlocked  = $user['is_blocked'];
                    } else {
                        $userId     = array_shift($args);
                        $name       = array_shift($args);
                        $isActive   = array_shift($args);
                        $isBlocked  = array_shift($args);
                    }

                    $attributes     = ['data-user-id' => $userId];
                    if ($isBlocked || !$isActive) {
                        $attributes['class'] = 'user-deleted';
                    }
                    return link_to_route('profile', $name, $userId, $attributes);
                },
                ['is_safe' => ['html']]
            ),

            new Twig_SimpleFunction(
                'can',
                function ($ability, $policy = null, $object = null) {
                    if (auth()->guest()) {
                        return false;
                    }

                    if ($policy === null) {
                        return $this->getGateFactory()->allows($ability);
                    }

                    return policy($policy)->$ability(auth()->user(), $policy, $object);
                }
            )
        ];
    }
}
