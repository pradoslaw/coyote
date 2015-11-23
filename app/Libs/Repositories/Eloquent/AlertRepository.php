<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Alert\Setting;
use Coyote\Alert\Sender;
use Coyote\Alert\Type;
use Coyote\Repositories\Contracts\AlertRepositoryInterface;

class AlertRepository extends Repository implements AlertRepositoryInterface
{
    public function model()
    {
        return 'Coyote\Alert';
    }

    public function headlinePattern($typeId)
    {
        return (new Type())->find($typeId, ['headline'])->pluck('headline');
    }

    public function userSettings($typeId, $usersId)
    {
        return (new Setting())->where('type_id', '=', $typeId)->whereIn('user_id', $usersId)->get();
    }

    /**
     * Pobiera pierwszy znaleziony alert odpowiadajacy ponizszemu zapytaniu (czyli nieczytany)
     * Metoda uzywana do grupowania nieprzeczytanych alertow
     *
     * @param int $userId
     * @param string $objectId
     * @param array $columns
     * @return mixed
     */
    public function findByObjectId($userId, $objectId, $columns = ['*'])
    {
        return $this->model->select($columns)
                ->where('user_id', '=', $userId)
                ->where('object_id', '=', $objectId)
                ->whereNull('read_at')
                ->first();
    }

    public function addSender($alertId, $userId, $senderName)
    {
        (new Sender())->create(['alert_id' => $alertId, 'user_id' => $userId, 'name' => $senderName]);
    }
}
