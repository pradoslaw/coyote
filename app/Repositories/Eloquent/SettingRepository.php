<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\SettingRepositoryInterface;

class SettingRepository extends Repository implements SettingRepositoryInterface
{
    /**
     * @return \Coyote\Setting
     */
    public function model()
    {
        return 'Coyote\Setting';
    }

    private function build($data)
    {
        if (!empty($data['user_id'])) {
            $data['session_id'] = null;
        }

        return $data;
    }

    /**
     * @param $name
     * @param $value
     * @param $userId
     * @param $sessionId
     */
    public function setItem($name, $value, $userId, $sessionId)
    {
        $model = $this->model;
        $where = $this->build(['name' => $name, 'user_id' => $userId, 'session_id' => $sessionId]);

        foreach ($where as $field => $val) {
            $model = $model->where($field, $val);
        }

        $sql = $model->update(['value' => $value]);

        if (!$sql) {
            $this->model->create(
                $this->build(['name' => $name, 'value' => $value, 'user_id' => $userId, 'session_id' => $sessionId])
            );
        }
    }

    /**
     * @param $name
     * @param $userId
     * @param $sessionId
     * @param null $default
     * @return null|static
     */
    public function getItem($name, $userId, $sessionId, $default = null)
    {
        $result = $this->findWhere($this->build(['name' => $name, 'user_id' => $userId, 'session_id' => $sessionId]));

        if (count($result)) {
            return (string) $result->pluck('value')[0];
        } else {
            return $default;
        }
    }

    /**
     * @param $userId
     * @param $sessionId
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function getAll($userId, $sessionId)
    {
        return $this->findWhere($this->build(['user_id' => $userId, 'session_id' => $sessionId]))
                    ->lists('value', 'name')
                    ->toArray();
    }
}
