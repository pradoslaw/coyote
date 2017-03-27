<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Guest;
use Coyote\Repositories\Contracts\GuestRepositoryInterface;
use Coyote\Session;

class GuestRepository extends Repository implements GuestRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Guest::class;
    }

    /**
     * @inheritdoc
     */
    public function store(Session $session): Guest
    {
        /** @var Guest $guest */
        $guest = $this->model->firstOrNew(
            $session->userId ? ['user_id' => $session->userId] : ['id' => $session->guestId]
        );

        $guest->id = $session->guestId;
        $guest->updated_at = Carbon::createFromTimestamp($session->updatedAt);

        if (!$guest->exists) {
            $guest->created_at = Carbon::createFromTimestamp($session->createdAt);
        }

        $guest->save();

        return $guest;
    }

    /**
     * @inheritdoc
     */
    public function createdAt($userId, $guestId = null): Carbon
    {
        static $dateTime;

        if (!empty($dateTime)) {
            return $dateTime;
        }

        if ($userId) {
            $result = $this->findBy('user_id', $userId, ['created_at']);
        } else {
            $result = $this->find($guestId, ['created_at']);
        }

        return $dateTime = ($result !== null ? $result->created_at : Carbon::now());
    }
}
