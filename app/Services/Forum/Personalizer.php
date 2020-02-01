<?php

namespace Coyote\Services\Forum;

use Coyote\Services\Session\Guest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class Personalizer
{
    /**
     * @var Guest
     */
    private $guest;

    /**
     * Personalizer constructor.
     * @param Guest $guest
     */
    public function __construct(Guest $guest)
    {
        $this->guest = $guest;
    }

    /**
     * @param LengthAwarePaginator $paginator
     * @return LengthAwarePaginator
     */
    public function markUnreadTopics(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        foreach ($paginator->items() as &$topic) {
            if (empty($topic->forum_marked_at)) {
                $topic->forum_marked_at = $this->guest->guessVisit();
            }

            /*
             * Jezeli data napisania ostatniego posta jest pozniejsza
             * niz data odznaczenia forum jako przeczytanego...
             * ORAZ
             * data napisania ostatniego postu jest pozniejsza niz data
             * ostatniego "czytania" tematu...
             * ODZNACZ JAKO NOWY
             */
            $topic->unread = $topic->last_created_at > $topic->forum_marked_at
                && $topic->last_created_at > $topic->topic_marked_at;
        }

        return $paginator;
    }
}
