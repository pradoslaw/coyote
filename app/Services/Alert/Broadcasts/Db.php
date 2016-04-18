<?php

namespace Coyote\Services\Alert\Broadcasts;

use Coyote\Services\Alert\Providers\ProviderInterface;
use Coyote\Events\AlertWasBroadcasted;
use Coyote\Repositories\Contracts\AlertRepositoryInterface;

/**
 * Class Db
 */
class Db extends Broadcast
{
    /**
     * @var AlertRepositoryInterface
     */
    protected $repository;

    /**
     * @var
     */
    protected $userId;

    /**
     * Db constructor.
     *
     * @param AlertRepositoryInterface $repository
     * @param $userId
     */
    public function __construct(AlertRepositoryInterface $repository, $userId)
    {
        $this->repository = $repository;
        $this->userId = $userId;
    }

    /**
     * @param ProviderInterface $alert
     */
    public function send(ProviderInterface $alert)
    {
        $result = $this->repository->findByObjectId($this->userId, $alert->objectId(), ['id']);

        if (!$result) {
            $object = $this->repository->create([
                'type_id'           => $alert->getTypeId(),
                'user_id'           => $this->userId,
                'subject'           => $alert->getSubject(),
                'excerpt'           => $alert->getExcerpt(),
                'url'               => $alert->getUrl(),
                'object_id'         => $alert->objectId()
            ]);

            $data = $alert->toArray();

            $data['headline'] = $this->parse($data, $data['headline']);
            // broadcast this event to save notification to redis
            event(new AlertWasBroadcasted($this->userId, $data));

            $alertId = $object->id;
        } else {
            $alertId = $result->id;
        }

        $this->repository->addSender($alertId, $alert->getSenderId(), $alert->getSenderName());
    }
}
