<?php
namespace Coyote\Services\TwigBridge\Extensions;

use Illuminate\Contracts\Auth\Access\Gate;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class User extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            // umozliwia szybki dostep do danych zalogowanego uzytkownika. zamiast:
            // auth()->user()->name mozemy napisac user('name')
            new TwigFunction('user', [$this, 'getUserValue'], ['is_safe' => ['html']]),

            // w calym serwisie ciagle trzeba generowac link do profilu usera. ta funkcja umozliwia
            // generowanie linku do profilu na podstawie dostarczonych parametrow. parametrzem moze
            // byc albo tablica albo poszczegolne dane takie jak ID, login oraz informacje czy uzytkownik
            // jest zablokowany lub zbanowany
            new TwigFunction('link_to_profile', [&$this, 'linkToProfile'], ['is_safe' => ['html']]),
            new TwigFunction('can', [&$this, 'can'], ['is_safe' => ['html']]),
        ];
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
            [$name, $key] = explode('.', $name);
        }
        $element = auth()->user()->$name;
        if (isset($key)) {
            $element = isset($element[$key]) ? $element[$key] : '';
        }
        return $element;
    }

    public function linkToProfile(...$args): string
    {
        $user = $args[0];
        if ($user instanceof \Coyote\User) {
            $user = $user->toArray();
        }
        if (is_array($user)) {
            $userId = isset($user['user_id']) ? $user['user_id'] : $user['id'];
            $name = isset($user['user_name']) ? $user['user_name'] : $user['name'];
            $isActive = !$user['deleted_at'];
            $isBlocked = $user['is_blocked'];
        } else {
            $userId = array_shift($args);
            $name = array_shift($args);
            $isActive = array_shift($args);
            $isBlocked = array_shift($args);
        }
        $attributes = ['data-user-id' => $userId];
        if ($isBlocked || !$isActive) {
            $attributes['class'] = 'user-deleted';
        }
        return link_to_route('profile', $name, $userId, $attributes);
    }

    /**
     * @param string $ability
     * @param null $policy
     * @param null $object
     * @return bool
     */
    public function can($ability, $policy = null, $object = null)
    {
        if (auth()->guest()) {
            return false;
        }
        if ($policy === null) {
            /** @var Gate $gate */
            $gate = app(Gate::class);
            return $gate->allows($ability);
        }
        return policy($policy)->$ability(auth()->user(), $policy, $object);
    }
}
