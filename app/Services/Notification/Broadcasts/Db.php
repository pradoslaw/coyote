<?php

namespace Coyote\Services\Notification\Broadcasts;

use Coyote\Services\Notification\Providers\ProviderInterface;
use Coyote\Events\NotificationWasBroadcasted;
use Coyote\Repositories\Contracts\NotificationRepositoryInterface as NotificationRepository;

/**
 * Class Db
 */
class Db extends Broadcast
{
    /**
     * @var NotificationRepository
     */
    protected $repository;

    /**
     * Db constructor.
     *
     * @param NotificationRepository $repository
     */
    public function __construct(NotificationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param mixed $user
     * @param ProviderInterface $notification
     * @return bool
     */
    public function send($user, ProviderInterface $notification)
    {
        $result = $this->repository->findByObjectId($user['user_id'], $notification->objectId(), ['id']);

        if (!$result) {
            $guid = str_random(25);
            $notification->setGuid($guid);

            $object = $this->repository->create([
                'type_id'           => $notification->getTypeId(),
                'user_id'           => $user['user_id'],
                'subject'           => $notification->getSubject(),
                'excerpt'           => $notification->getExcerpt(),
                'url'               => $notification->getUrl(),
                'object_id'         => $notification->objectId(),
                'guid'              => $guid
            ]);

            $data = $notification->toArray();

            $data['headline'] = $this->parse($data, $data['headline']);
            // broadcast this event to save notification to redis
            event(new NotificationWasBroadcasted($user['user_id'], $data));

            $notificationId = $object->id;
        } else {
            $notificationId = $result->id;
        }

        $this->repository->addSender($notificationId, $notification->getSenderId(), $notification->getSenderName());

        return true;
    }
}
