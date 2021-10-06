<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Requests\PollRequest;
use Coyote\Http\Resources\PollResource;
use Coyote\Poll;

class PollController extends BaseController
{
    /**
     * @param PollRequest $request
     * @param Poll $poll
     * @return PollResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function vote(PollRequest $request, Poll $poll)
    {
        $this->authorize('access', [$poll->topic->forum]);

        foreach ($request->get('items') as $itemId) {
            $poll->votes()->create([
                'item_id'       => $itemId,
                'user_id'       => $this->userId,
                'ip'            => $request->ip(),
                'fingerprint'   => request()->fingerprint
            ]);
        }

        PollResource::withoutWrapping();

        return new PollResource($poll);
    }
}
