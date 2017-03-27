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

        $guest->updated_at = Carbon::createFromTimestamp($session->updatedAt);

        if (empty($guest->created_at)) {
            $guest->created_at = Carbon::createFromTimestamp($session->createdAt);
        }

        $guest->save();

        return $guest;
    }
}
