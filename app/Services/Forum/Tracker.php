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
     * @var Guest
     */
    private $guest;

    /**
     * @var TopicRepository
     */
    private $repository;

    /**
     * @param Topic $model
     * @return static
     */
    public static function make(Topic $model): self
    {
        return app(static::class, ['model' => $model])->setRepository(app(TopicRepository::class));
    }

    /**
     * @param Topic $model
     * @param Guest $guest
     */
    public function __construct(Topic $model, Guest $guest)
    {
        $this->model = $model;
        $this->guest = $guest;
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
        $markTime = $this->model->markTime($this->guest->id);

        if (empty($markTime)) {
            $markTime = $this->model->forum->markTime($this->guest->id);
        }

        if (empty($markTime)) {
            $markTime = $this->guest->updated_at ?? $this->guest->getDefaultSessionTime();
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
        $this->model->markAsRead($date, $this->guest->id);

        if ($this->model->last_post_created_at > $date) {
            return;
        }

        // are there any unread topics in this category?
        $unread = $this->repository->countUnread(
            $this->model->forum->id,
            $this->model->forum->markTime($this->guest->id),
            $this->guest->id
        );

        if (!$unread) {
            $this->model->forum->markAsRead($date, $this->guest->id);
            // remove all unnecessary records from topic_track table
            $this->repository->flushRead($this->model->forum->id, $this->guest->id);
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
