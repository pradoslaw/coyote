<?php
namespace Coyote\Http\Validators;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\WithTrashed;

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
     */
    public function validateName($attribute, $value): bool
    {
        return pattern('^[0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ._ -]+$')->test($value);
    }

    /**
     * Check if login is already taken by another user (case insensitive)
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool TRUE if user name is not taken (FALSE otherwise)
     */
    public function validateUnique($attribute, $value, $parameters)
    {
        $this->user->pushCriteria(new WithTrashed());

        $result = $this->validateBy('name', $value, (int)($parameters[0] ?? null));
        $this->user->resetCriteria();

        return $result;
    }

    /**
     * Return TRUE if login exists in database
     *
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function validateExist($attribute, $value)
    {
        return $this->user->findByName(mb_strtolower($value)) !== null;
    }

    /**
     * @param string $column
     * @param string $value
     * @param null|int $userId
     * @return bool
     */
    protected function validateBy($column, $value, $userId = null)
    {
        // @see https://github.com/adam-boduch/coyote/issues/354
        $user = $this->user->{'findBy' . ucfirst($column)}(mb_strtolower($value));

        return ($user !== null && $userId !== $user->id) ? false : true;
    }
}
