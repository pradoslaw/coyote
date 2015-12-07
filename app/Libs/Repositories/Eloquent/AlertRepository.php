<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Alert\Setting;
use Coyote\Alert\Sender;
use Coyote\Alert\Type;
use Coyote\Repositories\Contracts\AlertRepositoryInterface;
use Coyote\Declination;

/**
 * Class AlertRepository
 * @package Coyote\Repositories\Eloquent
 */
class AlertRepository extends Repository implements AlertRepositoryInterface
{
    /**
     * @return \Coyote\Alert
     */
    public function model()
    {
        return 'Coyote\Alert';
    }

    /**
     * @param int $userId
     * @param int $perPage
     * @return mixed
     */
    public function paginate($userId, $perPage = 20)
    {
        $alerts = $this->prepare($userId, $perPage)->paginate($perPage);
        $alerts = $this->parse($alerts);

        return $alerts;
    }

    /**
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function takeForUser($userId, $limit = 10, $offset = 0)
    {
        $alerts = $this->prepare($userId)->take($limit)->skip($offset)->get();
        $alerts = $this->parse($alerts);

        return $alerts;
    }

    /**
     * Mark notifications as read
     *
     * @use Coyote\Http\Controllers\User\AlertsController
     * @param array $id
     */
    public function markAsRead(array $id)
    {
        $this->model->whereIn('id', $id)->update(['read_at' => Carbon::now()]);
    }

    /**
     * Find notification by url and mark it as read
     *
     * @param int $userId
     * @param string $url
     */
    public function markAsReadByUrl($userId, $url)
    {
        $this->model->where('user_id', $userId)
                    ->whereNull('read_at')
                    ->where('url', $url)
                    ->update(['read_at' => Carbon::now()]);
    }

    /**
     * Format notification's headline
     *
     * @param $alerts
     * @return mixed
     */
    private function parse($alerts)
    {
        $alerts->each(function ($alert) {
            $alert->user = $alert->senders->first();
            $count = $alert->senders->count();

            if ($count === 2 && $alert->user->name !== $alert->senders[1]->name) {
                $sender = $alert->user->name . ' (oraz ' . $alert->senders[1]->name . ')';
            } elseif ($count > 2) {
                $sender = $alert->user->name . ' (oraz ' . Declination::format($count, ['osoba', 'osoby', 'osób']) .')';
            } else {
                $sender = $alert->user->name;
            }

            $alert->headline = str_replace('{sender}', $sender, $alert->headline);
        });

        return $alerts;
    }

    /**
     * Build query for repository
     *
     * @param int $userId
     * @return mixed
     */
    private function prepare($userId)
    {
        return $this->model
                ->select(['alerts.*', 'alert_types.headline'])
                ->where('user_id', $userId)
                ->with(['senders' => function ($sql) {
                    $sql->select([
                        'alert_id',
                        'user_id',
                        \DB::raw('COALESCE(users.name, alert_senders.name) AS name'),
                        'photo',
                        'is_blocked',
                        'is_active'
                    ]);
                }])
                ->join('alert_types', 'alert_types.id', '=', 'type_id')
                ->orderBy('alerts.id', 'DESC');
    }

    /**
     * Gets notification headline for given type. This template is used for Db_Email() class for emails subject
     *
     * @param $typeId
     * @return mixed
     */
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
