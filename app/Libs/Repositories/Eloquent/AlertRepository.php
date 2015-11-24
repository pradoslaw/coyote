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

    public function takeForUser($userId, $limit = 10)
    {
        return $this->model
                ->where('user_id', $userId)
                ->with(['senders' => function ($sql) {
                    $sql->select(['users.name AS user_name', 'photo', 'is_blocked', 'is_active', 'alert_senders.name']);
                }])
                ->take($limit)
                ->get();
    }

    public function headlinePattern($typeId)
    {
        return (new Type())->find($typeId, ['headline'])['headline'];
    }

    /**
     * Gets notification settings for given user and notification type
     *
     * @param $typeId
     * @param $usersId
     * @return mixed
     */
    public function userSettings($typeId, $usersId)
    {
        return (new Setting())
                ->select(['alert_settings.*', 'users.email AS user_email', 'is_active', 'is_blocked', 'is_confirm'])
                ->where('type_id', '=', $typeId)
                ->whereIn('user_id', $usersId)
                ->join('users', 'users.id', '=', 'user_id')
                ->get();
    }

    /**
     * Gets first unread notification for given user and notification id (object_id)
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

    /**
     * One notification can have multiple senders (users). Few users can post comment to your post.
     * In that case notification can be grouped
     *
     * @param $alertId
     * @param $userId
     * @param $senderName
     */
    public function addSender($alertId, $userId, $senderName)
    {
        (new Sender())->create(['alert_id' => $alertId, 'user_id' => $userId, 'name' => $senderName]);
    }
}
