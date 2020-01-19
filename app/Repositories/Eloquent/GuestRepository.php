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
     * @inheritDoc
     */
    public function setSetting(string $name, string $value, string $guestId)
    {
        $value = $this->raw(sprintf('settings || \'{"%s": "%s"}\'', $name, $value));
        $sql = $this->model->where('id', $guestId)->update(['settings' => $value]);

        if (!$sql) {
            $this->model->create(['id' => $guestId, 'settings' => $value]);
        }
    }

    /**
     * @inheritDoc
     */
    public function getSettings(string $guestId)
    {
        return $this
            ->model
            ->select('settings')
            ->where('id', $guestId)
            ->value('settings');
    }

    /**
     * @inheritdoc
     */
    public function save(Session $session)
    {
        if (empty($session->guestId)) {
            return;
        }

        /** @var Guest $guest */
        $guest = $this->model->findOrNew($session->guestId);

        $guest->updated_at = Carbon::createFromTimestamp($session->updatedAt);
        // @todo mozna sprawdzac czy w tabeli users nie ma usera o guest_id = $session->guestId
        // dzieki temu ta kolumna bedzie zawsze wskazywala na prawidlowego usera
        $guest->user_id = $session->userId;

        if (!$guest->exists) {
            $guest->id = $session->guestId;
            $guest->created_at = Carbon::createFromTimestamp($session->createdAt);
        }

        $guest->save();
    }
}
