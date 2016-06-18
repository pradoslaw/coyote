<?php

namespace Coyote\Http\Validators;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;

/**
 * Class UserValidator
 */
class UserValidator
{
    /**
     * @var UserRepository
     */
    protected $user;
    
    /**
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    /**
     * Walidator sprawdza poprawnosc nazwy uzytkownika pod katem uzytych znakow. Nazwa uzytkownika
     * moze zawierac jedynie okreslony zbior znakow.
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return int
     */
    public function validateName($attribute, $value, $parameters, $validator)
    {
        return preg_match('/^[0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ._ -]+$/', $value);
    }

    /**
     * Check if login is already taken by another user (case insensitive)
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool TRUE if user name is not taken (FALSE otherwise)
     */
    public function validateUnique($attribute, $value, $parameters, $validator)
    {
        $userId = (int) $parameters[0] ?? null;
        $user = $this->user->findByName(mb_strtolower($value));

        if ($user && $userId !== $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Return TRUE if login exists in database
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function validateExist($attribute, $value, $parameters, $validator)
    {
        return $this->user->findByName(mb_strtolower($value)) !== null;
    }
}
