<?php

namespace Coyote\Services\Stream;

use Coyote\Events\StreamSaved;
use Coyote\Events\StreamSaving;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as StreamRepository;
use Coyote\Services\Stream\Activities\Activity;
use Coyote\Stream;
use Illuminate\Contracts\Auth\Guard;
use \Illuminate\Contracts\Events\Dispatcher;

class Manager
{
    /**
     * @var StreamRepository
     */
    protected $stream;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @param StreamRepository $stream
     * @param Dispatcher $events
     * @param Guard $auth
     */
    public function __construct(StreamRepository $stream, Dispatcher $events, Guard $auth)
    {
        $this->stream = $stream;
        $this->events = $events;
        $this->auth = $auth;
    }

    /**
     * @param Activity|string $activity
     * @param Objects\ObjectInterface|null $object
     * @param Objects\ObjectInterface|null $target
     * @return Stream
     */
    public function save($activity, $object = null, $target = null)
    {
        $activity = $this->getActivity($activity);

        if ($object !== null) {
            $activity->setObject($object);
        }
        if ($target !== null) {
            $activity->setTarget($target);
        }

        $this->events->dispatch(new StreamSaving($activity));

        $result =  $this->stream->create($activity->toArray());

        $this->events->dispatch(new StreamSaved($result));

        return $result;
    }

    /**
     * @param string|Activity $activity
     * @return Activity
     */
    protected function getActivity($activity): Activity
    {
        if (is_string($activity)) {
            $actor = new Actor($this->auth->user());

            $class = __NAMESPACE__ . '\\Activities\\' . ucfirst(camel_case(class_basename($activity)));
            $activity = new $class($actor);
        }

        return $activity;
    }
}
