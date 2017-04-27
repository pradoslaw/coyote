<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\User;

class UserRepository extends Repository implements UserRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return User::class;
    }

    /**
     * @param $name
     * @param array $userIds
     * @return mixed
     */
    public function lookupName($name, $userIds = [])
    {
        $sql = $this->model->select(['id', 'name', 'photo'])->where('name', 'ILIKE', $name . '%');

        if (!empty($userIds)) {
            $values = [];

            foreach ($userIds as $index => $userId) {
                $values[] = "($userId,$index)";
            }

            $sql->leftJoin(
                $this->raw('(VALUES ' . implode(',', $values) . ') AS x (user_id, ordering)'),
                'users.id',
                '=',
                'x.user_id'
            )
            ->orderBy($this->raw('CASE WHEN x.ordering IS NULL THEN 0 ELSE x.ordering END'), 'DESC');
        }

        return $sql->orderByRaw('visited_at DESC NULLS LAST')->limit(5)->get();
    }

    /**
     * Find by user name (case insensitive)
     *
     * @param $name
     * @return \Coyote\User|null
     */
    public function findByName($name)
    {
        return $this->getQueryBuilder('name', $name)->first();
    }

    /**
     * Find by user email (case insensitive). Return only user with confirmed email.
     *
     * @param $email
     * @return \Coyote\User|null
     */
    public function findByEmail($email)
    {
        return $this->getQueryBuilder('email', $email)->where('is_confirm', 1)->first();
    }

    /**
     * @param array $data
     * @return User
     */
    public function newUser(array $data)
    {
        return User::forceCreate($data);
    }

    /**
     * Pobiera reputacje usera w procentach (jak i rowniez pozycje usera w rankingu)
     *
     * @param $userId
     * @return null|array
     */
    public function rank($userId)
    {
        $sql = "SELECT u1.reputation AS reputation,
                (
                    u1.reputation::FLOAT / GREATEST(1, (

                        SELECT reputation
                        FROM users u2
                        ORDER BY u2.reputation DESC
                        LIMIT 1
                    )) * 100

                ) AS percentage,

                (
                    SELECT COUNT(*)
                    FROM users
                    WHERE reputation >= u1.reputation AND reputation > 0

                ) AS rank
                FROM users u1
                WHERE id = ?";

        $rowset = $this->app['db']->select($sql, [$userId]);

        // select() zwraca kolekcje. nas interesuje tylko jeden rekord
        if ($rowset) {
            return $rowset[0];
        } else {
            return null;
        }
    }

    /**
     * Podaje liczbe userow ktorzy maja jakakolwiek reputacje w systemie
     *
     * @return int
     */
    public function countUsersWithReputation()
    {
        return $this->model->where('reputation', '>', 0)->count();
    }

    /**
     * @param $field
     * @param $value
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQueryBuilder($field, $value)
    {
        return $this
            ->model
            ->select([
                'id',
                'name',
                'photo',
                'email',
                'is_active',
                'is_blocked',
                'is_confirm',
                'alert_failure',
                'access_ip'
            ])
            ->whereRaw("LOWER($field) = ?", [mb_strtolower($value)]);
    }
}
