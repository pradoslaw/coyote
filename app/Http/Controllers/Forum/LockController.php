<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Services\Stream\Activities\Lock as Stream_Lock;
use Coyote\Services\Stream\Activities\Unlock as Stream_Unlock;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Topic;

class LockController extends BaseController
{
    /**
     * @param Topic $topic
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Topic $topic)
    {
        $this->authorize('lock', $topic->forum);

        $this->transaction(function () use ($topic) {
            $topic->lock($this->userId);

            stream(
                $topic->is_locked ? Stream_Lock::class : Stream_Unlock::class,
                (new Stream_Topic())->map($topic)
            );
        });
    }
}
