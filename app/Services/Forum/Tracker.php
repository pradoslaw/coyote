<?php

namespace Coyote\Services\Forum;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\GuestRepositoryInterface as GuestRepository;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
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
     * @var GuestRepository
     */
    private $guest;

    /**
     * @param Topic $topic
     * @param TopicRepository $repository
     * @param GuestRepository $guest
     */
    public function __construct(Topic $topic, TopicRepository $repository, GuestRepository $guest)
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
            $markTime = $this->guessVisit($guestId);
        }

        return $markTime;
    }

    /**
     * @param string $guestId
     * @return bool
     */
    public function isRead(string $guestId): bool
    {
        return $this->topic->last_post_created_at >= $this->getMarkTime($guestId);
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

        $this->topic->forum->loadMarkTime($guestId);

        // are there any unread topics in this category?
        $unread = $this->repository->countUnread(
            $this->topic->forum->id,
            $this->topic->forum->read_at,
            $guestId
        );

        if (!$unread) {
            $this->topic->forum->markAsRead($guestId);
            $this->repository->flushRead($this->topic->forum->id, $guestId);
        }
    }

    private function guessVisit(string $guestId): Carbon
    {
        static $createdAt;

        if (!empty($createdAt)) {
            return $createdAt;
        }

        $result = $this->guest->find($guestId, ['created_at']);

        if ($result === null) {
            $createdAt = Carbon::now();
        } else {
            $createdAt = $result->created_at;
        }

        return $createdAt;
    }
}
