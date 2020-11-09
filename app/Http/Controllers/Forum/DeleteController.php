<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Forum\Reason;
use Coyote\Events\TopicWasDeleted;
use Coyote\Events\PostWasDeleted;
use Coyote\Notifications\Topic\DeletedNotification as TopicDeletedNotification;
use Coyote\Notifications\Post\DeletedNotification as PostDeletedNotification;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Forum as Stream_Forum;
use Coyote\Services\UrlBuilder;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Http\Request;

class DeleteController extends BaseController
{
    /**
     * Delete post or whole thread
     *
     * @param \Coyote\Post $post
     * @param Request $request
     * @param Dispatcher $dispatcher
     */
    public function index($post, Request $request, Dispatcher $dispatcher)
    {
        // it must be like that. only if reason has been chosen, we need to validate it.
        if ($request->get('reason')) {
            $this->validate($request, ['reason' => 'int|exists:forum_reasons,id']);
        }

        // Step 1. Get post category
        $forum = &$post->forum;

        // Step 2. Does user really have permission to delete this post? Maybe topic or forum is locked
        $this->authorize('delete', [$post]);

        // Step 3. Maybe user does not have an access to this category?
        $this->authorize('access', [$forum]);

        $topic = &$post->topic;

        $this->transaction(function () use ($post, $topic, $forum, $request, $dispatcher) {
            $url = UrlBuilder::topic($topic);

            $reason = new Reason();

            if ($request->get('reason')) {
                $reason = Reason::find($request->get('reason'));
            }

            // if this is the first post in topic... we must delete whole thread
            if ($post->id === $topic->first_post_id) {
                $subscribers = $topic->subscribers()->with('user')->get()->pluck('user');

                if ($post->user_id !== null) {
                    $subscribers = $subscribers->push($post->user)->unique('id'); // add post's author to notification subscribers
                }

                $topic->delete();

                $dispatcher->send(
                    $subscribers->exceptUser($this->auth),
                    (new TopicDeletedNotification($this->auth, $topic))
                        ->setReasonText($reason->description)
                        ->setReasonName($reason->name)
                );

                // fire the event. it can be used to delete row from "pages" table or from search index
                event(new TopicWasDeleted($topic));

                $object = (new Stream_Topic())->map($topic);
                $target = (new Stream_Forum())->map($forum);
            } else {
                $subscribers = $post->subscribers()->with('user')->get()->pluck('user');

                if ($post->user_id !== null) {
                    $subscribers = $subscribers->push($post->user)->unique('id');
                }

                $post->deleteWithReason($this->userId, $reason->name);

                $dispatcher->send(
                    $subscribers->exceptUser($this->auth),
                    (new PostDeletedNotification($this->auth, $post))
                        ->setReasonName($reason->name)
                        ->setReasonText($reason->description)
                );

                $url .= '?p=' . $post->id . '#id' . $post->id;

                // fire the event. delete from search index
                event(new PostWasDeleted($post));

                $object = (new Stream_Post(['url' => $url]))->map($post);
                $target = (new Stream_Topic())->map($topic);
            }

            if (!empty($reason)) {
                $object->reasonName = $reason->name;
            }

            stream(Stream_Delete::class, $object, $target);
        });
    }
}
