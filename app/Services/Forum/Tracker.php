<?php

namespace Coyote\Services\Forum;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Services\Session\Guest;
use Coyote\Topic;

class Tracker
{
    /**
     * @var Topic
     */
    private $topic;

    /**
     * @var TopicRepository
     */
    private $repository;

    /**
     * @var Guest
     */
    private $guest;

    /**
     * @param Topic $topic
     * @param TopicRepository $repository
     * @param Guest $guest
     */
    public function __construct(Topic $topic, TopicRepository $repository, Guest $guest)
    {
        $this->topic = $topic;
        $this->guest = $guest;
        $this->repository = $repository;
    }

    /**
     * @param Topic $topic
     * @return static
     */
    public static function make(Topic $topic): self
    {
        return app(static::class, ['topic' => $topic]);
    }

    /**
     * @param string $guestId
     * @return Carbon
     */
    public function getMarkTime(string $guestId): Carbon
    {
        $this->topic->loadMarkTime($guestId);
        $markTime = $this->topic->read_at;

        if (empty($markTime)) {
            $this->topic->forum->loadMarkTime($guestId);
            $markTime = $this->topic->forum->read_at;
        }

        if (empty($markTime)) {
            $markTime = $this->guest->guessVisit();
        }

        return $markTime;
    }

    /**
     * @param string $guestId
     * @return bool
     */
    public function isRead(string $guestId): bool
    {
        return $this->topic->last_post_created_at > $this->getMarkTime($guestId);
    }

    /**
     * @param string $guestId
     * @param $date
     */
    public function asRead(string $guestId, $date)
    {
        $this->topic->markAsRead($date, $guestId);

        if ($this->topic->last_post_created_at > $date) {
            return;
        }

        $markTime = $this->topic->forum->markTime($guestId);

        // are there any unread topics in this category?
        $unread = $this->repository->countUnread(
            $this->topic->forum->id,
            $markTime,
            $guestId
        );

        if (!$unread) {
            $this->topic->forum->markAsRead($guestId);
            $this->repository->flushRead($this->topic->forum->id, $guestId);
        }
    }
}
