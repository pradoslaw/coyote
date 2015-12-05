<?php

/**
 * @param null $activity
 * @param null $object
 * @param null $target
 * @return \Coyote\Stream\Stream
 */
function stream($activity = null, $object = null, $target = null)
{
    $repository = app()->make('Coyote\\Repositories\\Contracts\\StreamRepositoryInterface');
    $stream = new Coyote\Stream\Stream($repository);

    if ($activity) {
        if (is_string($activity)) {
            $actor = new Coyote\Stream\Actor(auth()->user());

            $class = 'Coyote\\Stream\\Activities\\' . ucfirst(camel_case(class_basename($activity)));
            $stream->add(new $class($actor, $object, $target));
        } else {
            $stream->add($activity);
        }
    }

    return $stream;
}
