<?php

namespace Coyote\Parser\Providers;

use Coyote\User;

/**
 * Parser ktory zamienia @nick na dzialajacy link do profilu usera
 *
 * Class Username
 * @package Coyote\Parser\Providers
 */
class Username extends Provider implements ProviderInterface
{
    /**
     * Wyrazenia regularne ktore "wyciagaja" z tekstu loginy uzytkownikow
     *
     * @var array
     */
    private $patterns = [
        '~(@([0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ=|#_ ()[\]^-]+)):~',
        '~(?<!">|" >)(@([0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ=|#_()[\]^-]+))~',
        '~(@{([0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ=|#_ .()[\]^-]+))}~'
    ];

    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Parsowanie tekstu i zamiana loginow uzytkownikow na dzialajace linki do profilu
     *
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        return $this->processInline($text, function ($line) {
            foreach ($this->patterns as $pattern) {
                preg_match_all($pattern, $line, $matches);

                $origins = &$matches[0];
                $logins  = &$matches[2];

                if ($logins) {
                    foreach ($logins as $idx => $login) {
                        $user = $this->findByName($login);

                        if ($user) {
                            $line = str_ireplace(
                                $origins[$idx],
                                link_to_route('profile', '@' . $user->name, [$user->id], ['data-user-id' => $user->id]),
                                $line
                            );
                        }
                    }
                }
            }

            return $line;
        });
    }

    /**
     * Sprawdzanie czy nazwa uzytkownika istnieje w bazie. Jezeli tak, to zwracany jest obiekt zawierajacy
     * ID oraz login uzytkownika
     *
     * @param $name
     * @return mixed
     */
    private function findByName($name)
    {
        static $cache = [];

        if (isset($cache[$name])) {
            return $cache[$name];
        } else {
            return $cache[$name] = $this->user->select(['id', 'name'])->where('name', $name)->first();
        }
    }

    /**
     * Sprawdza czy uzytkownicy o podanych loginach znajduja sie w bazie danych.
     *
     * @param array $names
     * @return mixed
     */
    private function findByNames(array $names)
    {
        return $this->user->select(['id', 'name'])->whereIn('name', $names)->get()->lists('name', 'id');
    }

    /**
     * Metoda wyszukuje odwolan do loginow uzytkownikow. Zwracane sa tylko te loginy ktore
     * faktycznie istnieja w bazie danych. Jest to potrzebne do wysylania powiadomien
     * @param string $text
     * @return mixed
     */
    public function find($text)
    {
        $logins = [];

        $this->processInline($text, function ($line) use (&$logins) {
            foreach ($this->patterns as $pattern) {
                preg_match_all($pattern, $line, $matches);

                $logins = array_merge($logins, $matches[2]);
            }
        });

        $logins = array_unique($logins);
        return $this->findByNames($logins);
    }
}
