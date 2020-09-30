<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Resources\PollResource;
use Coyote\Repositories\Contracts\PollRepositoryInterface as PollRepository;
use Illuminate\Http\Request;

class PollController extends BaseController
{
    /**
     * @param Request $request
     * @param PollRepository $repository
     * @param int $pollId
     * @return PollResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function vote(Request $request, PollRepository $repository, int $pollId)
    {
        /** @var \Coyote\Poll $poll */
        $poll = $repository->findOrFail($pollId);
        $items = $poll->items()->pluck('id');

        $this->authorize('access', [$poll->topic->forum]);

        $this->validate($request, [
            'items' => 'required|array|max:' . $poll->max_items,
            'items.*' => 'required|integer|in:' . $items->implode(',')
        ]);

        abort_if($poll->votes()->pluck('user_id')->contains($this->userId), 500);

        foreach ($request->get('items') as $itemId) {
            $poll->votes()->create([
                'item_id'   => $itemId,
                'user_id'   => $this->userId,
                'poll_id'   => $pollId,
                'ip'        => $request->ip()
            ]);
        }

        PollResource::withoutWrapping();

        return new PollResource($poll);
    }
}
