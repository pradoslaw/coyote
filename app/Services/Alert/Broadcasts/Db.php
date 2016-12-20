<?php

namespace Coyote\Services\Alert\Broadcasts;

use Coyote\Services\Alert\Providers\ProviderInterface;
use Coyote\Events\AlertWasBroadcasted;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as AlertRepository;

/**
 * Class Db
 */
class Db extends Broadcast
{
    /**
     * @var AlertRepository
     */
    protected $repository;

    /**
     * Db constructor.
     *
     * @param AlertRepository $repository
     */
    public function __construct(AlertRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param mixed $user
     * @param ProviderInterface $alert
     * @return bool
     */
    public function send($user, ProviderInterface $alert)
    {
        $result = $this->repository->findByObjectId($user['user_id'], $alert->objectId(), ['id']);

        if (!$result) {
            $guid = str_random(25);
            $alert->setGuid($guid);

            $object = $this->repository->create([
                'type_id'           => $alert->getTypeId(),
                'user_id'           => $user['user_id'],
                'subject'           => $alert->getSubject(),
                'excerpt'           => $alert->getExcerpt(),
                'url'               => $alert->getUrl(),
                'object_id'         => $alert->objectId(),
                'guid'              => $guid
            ]);

            $data = $alert->toArray();

            $data['headline'] = $this->parse($data, $data['headline']);
            // broadcast this event to save notification to redis
            event(new AlertWasBroadcasted($user['user_id'], $data));

            $alertId = $object->id;
        } else {
            $alertId = $result->id;
        }

        $this->repository->addSender($alertId, $alert->getSenderId(), $alert->getSenderName());

        return true;
    }
}
