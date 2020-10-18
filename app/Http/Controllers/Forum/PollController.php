<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Resources\PollResource;
use Coyote\Poll;
use Illuminate\Http\Request;

class PollController extends BaseController
{
    /**
     * @param Request $request
     * @param Poll $poll
     * @return PollResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function vote(Request $request, Poll $poll)
    {
        $this->authorize('access', [$poll->topic->forum]);

        $this->validate($request, [
            'items' => 'required|array|max:' . $poll->max_items,
            'items.*' => 'required|integer|in:' . $poll->items()->pluck('id')->implode(',')
        ]);

        abort_if($poll->votes()->forUser($this->userId)->exists(), 500);

        foreach ($request->get('items') as $itemId) {
            $poll->votes()->create([
                'item_id'   => $itemId,
                'user_id'   => $this->userId,
                'ip'        => $request->ip()
            ]);
        }

        PollResource::withoutWrapping();

        return new PollResource($poll);
    }
}
