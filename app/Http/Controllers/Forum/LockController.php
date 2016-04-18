<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Services\Stream\Activities\Lock as Stream_Lock;
use Coyote\Services\Stream\Activities\Unlock as Stream_Unlock;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;

class LockController extends BaseController
{
    /**
     * @param \Coyote\Topic $topic
     */
    public function index($topic)
    {
        $forum = $topic->forum()->first();
        $this->authorize('lock', $forum);

        \DB::transaction(function () use ($topic, $forum) {
            $topic->lock();

            stream(
                $topic->is_locked ? Stream_Lock::class : Stream_Unlock::class,
                (new Stream_Topic())->map($topic, $forum)
            );
        });
    }
}
