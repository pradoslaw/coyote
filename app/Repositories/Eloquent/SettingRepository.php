<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\SettingRepositoryInterface;

class SettingRepository extends Repository implements SettingRepositoryInterface
{
    /**
     * @return string
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
     * @inheritDoc
     */
    public function setItem($name, $value, $guestId)
    {
        $sql = $this->model->where('name', $name)->where('guest_id', $guestId)->update(['value' => $value]);

        if (!$sql) {
            $this->model->create(
                $this->build(['name' => $name, 'value' => $value, 'guest_id' => $guestId])
            );
        }
    }

    /**
     * @param $name
     * @param $guestId
     * @param null $default
     * @return string
     */
    public function getItem($name, $guestId, $default = null)
    {
        $result = $this->findWhere(['name' => $name, 'guest_id' => $guestId]);

        if (count($result)) {
            return (string) $result->pluck('value')[0];
        } else {
            return $default;
        }
    }

    /**
     * @inheritDoc
     */
    public function getAll($guestId)
    {
        return $this->model
                    ->where('guest_id', $guestId)
                    ->pluck('value', 'name')
                    ->toArray();
    }
}
