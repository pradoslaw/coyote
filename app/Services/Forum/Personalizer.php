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

    /**
     * @param Collection $categories
     * @return Collection
     */
    public function markUnreadCategories(Collection $categories)
    {
        // loop for each category
        foreach ($categories as &$row) {
            if (empty($row->forum_marked_at)) {
                $row->forum_marked_at = $this->guest->guessVisit();
            }

            // are there any new posts (since I last marked category as read)?
            $row->forum_unread = $row->created_at > $row->forum_marked_at;
            $row->topic_unread = $row->created_at > $row->topic_marked_at && $row->forum_unread;
            $row->route = route('forum.topic', [$row->slug, $row->topic_id, $row->topic_slug]);
        }

        return $categories;
    }
}
