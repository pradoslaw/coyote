<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Activities\Move as Stream_Move;
use Coyote\Services\Stream\Objects\Forum as Stream_Forum;
use Coyote\Events\TopicWasMoved;
use Coyote\Forum\Reason;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Http\Request;

class MoveController extends BaseController
{
    /**
     * @param \Coyote\Topic $topic
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index($topic, Request $request)
    {
        $rules = ['slug' => 'required|exists:forums'];

        // it must be like that. only if reason has been chosen, we need to validate it.
        if ($request->get('reason')) {
            $rules['reason'] = 'int|exists:forum_reasons,id';
        }
        $this->validate($request, $rules);

        $old = $topic->forum; // old category

        $this->authorize('move', $old);
        $forum = $this->forum->findBy('slug', $request->get('slug'));

        if (!$forum->userCanAccess($this->userId)) {
            abort(401);
        }

        $this->transaction(function () use ($topic, $forum, $request) {
            $reason = null;

            $notification = [
                'sender_id'   => $this->userId,
                'sender_name' => auth()->user()->name,
                'subject'     => excerpt($topic->subject),
                'forum'       => $forum->name
            ];

            if ($request->get('reason')) {
                $reason = Reason::find($request->get('reason'));

                $notification = array_merge($notification, [
                    'excerpt'       => $reason->name,
                    'reasonName'    => $reason->name,
                    'reasonText'    => $reason->description
                ]);
            }

            // first, create object. we will save it in mongodb.
            $object = (new Stream_Topic())->map($topic);

            // then, set a new forum id
            $topic->forum_id = $forum->id;
            // magic happens here. database trigger will do the work
            $topic->save();

            if (!empty($reason)) {
                $object->reasonName = $reason->name;
            }

            $post = $this->post->find($topic->first_post_id, ['user_id']);
            $recipientsId = $forum->onlyUsersWithAccess([$post->user_id]);

            if ($recipientsId) {
                app('alert.topic.move')
                    ->with($notification)
                    ->setUrl(UrlBuilder::topic($topic))
                    ->setUsersId($recipientsId)
                    ->notify();
            }

            // we need to reindex this topic
            event(new TopicWasMoved($topic));
            stream(Stream_Move::class, $object, (new Stream_Forum())->map($forum));
        });

        return redirect()->to(UrlBuilder::topic($topic))->with('success', 'Wątek został przeniesiony');
    }
}
