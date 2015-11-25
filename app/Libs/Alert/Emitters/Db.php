<?php

namespace Coyote\Alert\Emitters;

use Coyote\Alert\Providers\ProviderInterface;
use Coyote\Repositories\Contracts\AlertRepositoryInterface;

class Db extends Emitter
{
    protected $repository;
    protected $userId;

    public function __construct(AlertRepositoryInterface $repository, $userId)
    {
        $this->repository = $repository;
        $this->userId = $userId;
    }

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

            $alertId = $object->id;
        } else {
            $alertId = $result->id;
        }

        $this->repository->addSender($alertId, $alert->getSenderId(), $alert->getSenderName());
    }
}
