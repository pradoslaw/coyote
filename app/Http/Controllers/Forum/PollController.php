<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\PollRepositoryInterface;
use Illuminate\Http\Request;

class PollController extends BaseController
{
    /**
     * @param Request $request
     * @param \Coyote\Forum $forum
     * @param int $pollId
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function vote(Request $request, $forum, $pollId)
    {
        /** @var \Coyote\Poll $poll */
        $poll = $this->getPollRepository()->findOrFail($pollId);
        $items = $poll->items()->pluck('id');

        $this->validate($request, [
            'items' => 'required|array|max:' . $poll->max_items,
            'items.*' => 'required|integer|in:' . $items->implode(',')
        ]);

        if ($poll->votes()->pluck('user_id')->contains($this->userId)) {
            abort(500);
        }

        foreach ($request->get('items') as $itemId) {
            $poll->votes()->create([
                'item_id' => $itemId,
                'user_id' => $this->userId,
                'poll_id' => $pollId,
                'ip' => $request->ip()
            ]);
        }

        return view('forum.partials.poll', ['forum' => ['slug' => $forum->slug], 'topic' => [
            'is_locked' => false,
            'poll_id' => $pollId,
            'poll' => $poll
        ]]);
    }

    /**
     * @return PollRepositoryInterface
     */
    private function getPollRepository()
    {
        return app(PollRepositoryInterface::class);
    }
}
