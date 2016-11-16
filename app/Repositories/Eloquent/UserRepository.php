<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\UserRepositoryInterface;

class UserRepository extends Repository implements UserRepositoryInterface
{
    public function model()
    {
        return 'Coyote\User';
    }

    /**
     * @param $name
     * @param array $orderByUsersId
     * @return mixed
     */
    public function lookupName($name, $orderByUsersId = [])
    {
        $sql = $this->model->select(['id', 'name', 'photo'])->where('name', 'ILIKE', $name . '%');
        if (!empty($orderByUsersId)) {
            // @todo To moze nie zadzialac na postgresie
            $sql->orderBy($this->raw('id IN(' . implode(',', $orderByUsersId) . ')'), 'DESC');
        }

        return $sql->orderBy('visited_at', 'DESC')->limit(5)->get();
    }

    /**
     * Find by user name (case insensitive)
     *
     * @param $name
     * @return \Coyote\User|\Illuminate\Database\Query\Builder|null
     */
    public function findByName($name)
    {
        return $this->getQueryBuilder('name', $name)->first();
    }

    /**
     * Find by user email (case insensitive). Return only user with confirmed email.
     *
     * @param $email
     * @return \Coyote\User|\Illuminate\Database\Query\Builder|null
     */
    public function findByEmail($email)
    {
        return $this->getQueryBuilder('email', $email)->where('is_confirm', 1)->first();
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

        $rowset = \DB::select($sql, [$userId]);

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
            ->select(['id', 'name', 'photo', 'email', 'is_active', 'is_blocked', 'is_confirm', 'alert_failure'])
            ->whereRaw("LOWER($field) = ?", [mb_strtolower($value)]);
    }
}
