<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Notifications\Topic\MovedNotification;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Activities\Move as Stream_Move;
use Coyote\Services\Stream\Objects\Forum as Stream_Forum;
use Coyote\Events\TopicWasMoved;
use Coyote\Forum\Reason;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Illuminate\Http\Request;

class MoveController extends BaseController
{
    /**
     * @param Topic $topic
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Topic $topic, Request $request)
    {
        $this->validate($request, ['id' => 'required|exists:forums', 'reason_id' => 'nullable|int|exists:forum_reasons,id']);

        $old = $topic->forum; // old category

        $this->authorize('move', $old);
        $forum = $this->forum->find($request->input('id'));

        $this->authorize('access', $forum);

        abort_if($old->id === $forum->id, 404);

        $this->transaction(function () use ($topic, $forum, $request) {
            $reason = new Reason();

            if ($request->filled('reason_id')) {
                $reason = Reason::find($request->get('reason_id'));
            }

            // first, create object. we will save it in db.
            $object = (new Stream_Topic())->map($topic);

            // save old forum id
            $topic->prev_forum_id = $topic->forum_id;
            // then, set a new forum id
            $topic->forum_id = $forum->id;
            $topic->mover_id = $this->userId;
            $topic->moved_at = $topic->freshTimestamp();

            // magic happens here. database trigger will do the work
            $topic->save();

            if (!empty($reason)) {
                $object->reasonName = $reason->name;
            }

            /** @var \Coyote\Post $post */
            $post = $this->post->find($topic->first_post_id, ['user_id']);

            if ($post->user !== null) {
                $post->user->notify(
                    (new MovedNotification($this->auth, $topic))
                        ->setReasonText($reason->description)
                        ->setReasonName($reason->name)
                );
            }

            // we need to reindex this topic
            event(new TopicWasMoved($topic));
            stream(Stream_Move::class, $object, (new Stream_Forum())->map($forum));
        });

        session()->flash('success', 'Wątek został przeniesiony');

        return response()->json(['url' => UrlBuilder::topic($topic)]);
    }
}
