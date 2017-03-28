<?php

namespace Coyote\Services\Forum;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\GuestRepositoryInterface as GuestRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class Personalizer
{
    /**
     * @var GuestRepository
     */
    protected $guest;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param GuestRepository $guest
     * @param Request $request
     */
    public function __construct(GuestRepository $guest, Request $request)
    {
        $this->guest = $guest;
        $this->request = $request;
    }

    /**
     * @param LengthAwarePaginator $paginator
     * @return LengthAwarePaginator
     */
    public function markUnreadTopics(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        foreach ($paginator->items() as &$topic) {
            if (empty($topic->forum_marked_at)) {
                $topic->forum_marked_at = $this->getDefaultDateTime();
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
     * @param Collection $sections
     * @return Collection
     */
    public function markUnreadCategories(Collection $sections)
    {
        foreach ($sections as $section) {
            // loop for each category (even subcategories)
            foreach ($section as &$row) {
                if (empty($row->forum_marked_at)) {
                    $row->forum_marked_at = $this->getDefaultDateTime();
                }

                // are there any new posts (since I last marked category as read)?
                $row->forum_unread = $row->created_at > $row->forum_marked_at;
                $row->topic_unread = $row->created_at > $row->topic_marked_at && $row->forum_unread;
                $row->route = route('forum.topic', [$row->slug, $row->topic_id, $row->topic_slug]);
            }
        }

        return $sections;
    }

    /**
     * @return Carbon
     */
    public function getDefaultDateTime(): Carbon
    {
        static $createdAt;

        if (!empty($createdAt)) {
            return $createdAt;
        }

        $createdAt = $this->guest->createdAt(
            $this->request->session()->get('user_id'),
            $this->request->session()->get('guest_id')
        );

        if ($createdAt === null) {
            $createdAt = Carbon::createFromTimestamp($this->request->session()->get('created_at'));
        }

        return $createdAt;
    }
}
