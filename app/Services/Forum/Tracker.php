<?php

namespace Coyote\Services\Forum;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Services\Guest;
use Coyote\Topic;
use Illuminate\Support\Traits\ForwardsCalls;

class Tracker
{
    use ForwardsCalls;

    /**
     * @var Topic
     */
    private $model;

    /**
     * @var string|null
     */
    private $guestId;

    /**
     * @var TopicRepository
     */
    private $repository;

    /**
     * @param Topic $model
     * @param string|null $guestId
     * @return static
     */
    public static function make(Topic $model, ?string $guestId): self
    {
        return (new self($model, $guestId))->setRepository(app(TopicRepository::class));
    }

    /**
     * @param Topic $model
     * @param string|null $guestId
     */
    public function __construct(Topic $model, ?string $guestId)
    {
        $this->model = $model;
        $this->guestId = $guestId;
    }

    /**
     * @return Topic
     */
    public function getModel(): Topic
    {
        return $this->model;
    }

    /**
     * @param TopicRepository $repository
     * @return $this
     */
    public function setRepository(TopicRepository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getMarkTime(): Carbon
    {
        $markTime = $this->model->markTime($this->guestId);

        if (empty($markTime)) {
            $markTime = $this->model->forum->markTime($this->guestId);
        }

        if (empty($markTime)) {
            $guest = app(Guest::class);

            $markTime = $guest->created_at ?? now('UTC');
        }

        return $markTime;
    }

    /**
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->getMarkTime() >= $this->model->last_post_created_at;
    }

    /**
     * @param $date
     */
    public function asRead($date)
    {
        $this->model->markAsRead($date, $this->guestId);

        if ($this->model->last_post_created_at > $date) {
            return;
        }

        // are there any unread topics in this category?
        $unread = $this->repository->countUnread(
            $this->model->forum->id,
            $this->model->forum->markTime($this->guestId),
            $this->guestId
        );

        if (!$unread) {
            $this->model->forum->markAsRead($date, $this->guestId);
            // remove all unnecessary records from topic_track table
            $this->repository->flushRead($this->model->forum->id, $this->guestId);
        }
    }

    /**
     * Dynamically get properties from the underlying model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->model->{$key};
    }

    /**
     * Dynamically pass method calls to the underlying model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->model, $method, $parameters);
    }
}
