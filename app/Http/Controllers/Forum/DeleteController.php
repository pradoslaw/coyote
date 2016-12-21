<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Forum\Reason;
use Coyote\Events\TopicWasDeleted;
use Coyote\Events\PostWasDeleted;
use Coyote\Http\Factories\FlagFactory;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Forum as Stream_Forum;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Http\Request;

class DeleteController extends BaseController
{
    use FlagFactory;

    /**
     * Delete post or whole thread
     *
     * @param \Coyote\Post $post
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index($post, Request $request)
    {
        // it must be like that. only if reason has been chosen, we need to validate it.
        if ($request->get('reason')) {
            $this->validate($request, ['reason' => 'int|exists:forum_reasons,id']);
        }

        // Step 1. Get post category
        $forum = &$post->forum;

        // Step 2. Does user really have permission to delete this post?
        $this->authorize('delete', [$post, $forum]);

        // Step 3. Maybe user does not have an access to this category?
        $forum->userCanAccess($this->userId) || abort(401, 'Unauthorized');
        $topic = &$post->topic;

        // Step 4. Only moderators can delete this post if topic (or forum) was locked
        if ($this->getGateFactory()->denies('delete', $forum)) {
            if ($topic->is_locked || $forum->is_locked || $post->id < $topic->last_post_id || $post->deleted_at) {
                abort(401, 'Unauthorized');
            }
        }

        $url = $this->transaction(function () use ($post, $topic, $forum, $request) {
            $url = UrlBuilder::topic($topic);

            $notification = [
                'sender_id'   => $this->userId,
                'sender_name' => $this->auth->name,
                'subject'     => str_limit($topic->subject, 84)
            ];

            $reason = null;

            if ($request->get('reason')) {
                $reason = Reason::find($request->get('reason'));

                $notification = array_merge($notification, [
                    'excerpt'       => $reason->name,
                    'reasonName'    => $reason->name,
                    'reasonText'    => $reason->description
                ]);
            }

            // if this is the first post in topic... we must delete whole thread
            if ($post->id === $topic->first_post_id) {
                $redirect = redirect()->route('forum.category', [$forum->slug]);

                $subscribersId = $topic->subscribers()->lists('user_id');
                if ($post->user_id !== null) {
                    $subscribersId[] = $post->user_id;
                }

                $topic->delete();
                // delete topic's flag
                $this->getFlagFactory()->deleteBy('topic_id', $topic->id, $this->userId);

                if ($subscribersId) {
                    app('alert.topic.delete')
                        ->with($notification)
                        ->setUsersId($subscribersId->toArray())
                        ->notify();
                }

                // fire the event. it can be used to delete row from "pages" table or from search index
                event(new TopicWasDeleted($topic));

                $object = (new Stream_Topic())->map($topic);
                $target = (new Stream_Forum())->map($forum);
            } else {
                $subscribersId = $post->subscribers()->lists('user_id');

                if ($post->user_id !== null) {
                    $subscribersId[] = $post->user_id;
                }

                $post->delete();
                // delete post's flags
                $this->getFlagFactory()->deleteBy('post_id', $post->id, $this->userId);

                if ($subscribersId) {
                    app('alert.post.delete')
                        ->with($notification)
                        ->setUrl($url)
                        ->setUsersId($subscribersId->toArray())
                        ->notify();
                }

                $url .= '?p=' . $post->id . '#id' . $post->id;

                $redirect = back();
                // fire the event. delete from search index
                event(new PostWasDeleted($post));

                $object = (new Stream_Post(['url' => $url]))->map($post);
                $target = (new Stream_Topic())->map($topic);
            }

            if (!empty($reason)) {
                $object->reasonName = $reason->name;
            }

            stream(Stream_Delete::class, $object, $target);
            return $redirect->with('success', 'Post został usunięty.');
        });

        return $url;
    }
}
