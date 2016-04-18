<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Forum\Reason;
use Coyote\Events\TopicWasDeleted;
use Coyote\Events\PostWasDeleted;
use Coyote\Repositories\Contracts\FlagRepositoryInterface;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Activities\Restore as Stream_Restore;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Forum as Stream_Forum;
use Coyote\Events\PostWasSaved;
use Coyote\Events\TopicWasSaved;
use Illuminate\Http\Request;

class DeleteController extends BaseController
{
    /**
     * Delete post or whole thread
     *
     * @param int $id post id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index($id, Request $request)
    {
        // it must be like that. only if reason has been chosen, we need to validate it.
        if ($request->get('reason')) {
            $this->validate($request, ['reason' => 'int|exists:forum_reasons,id']);
        }

        // Step 1. Does post really exist?
        $post = $this->post->withTrashed()->findOrFail($id);
        $forum = $this->forum->find($post->forum_id);

        // Step 2. Does user really have permission to delete this post?
        $this->authorize('delete', [$post, $forum]);

        // Step 3. Maybe user does not have an access to this category?
        if (!$forum->userCanAccess($this->userId)) {
            abort(401, 'Unauthorized');
        }

        $topic = $this->topic->withTrashed()->find($post->topic_id);

        // Step 4. Only moderators can delete this post if topic (or forum) was locked
        if ($this->getGateFactory()->denies('delete', $forum)) {
            if ($topic->is_locked || $forum->is_locked || $post->id < $topic->last_post_id || $post->deleted_at) {
                abort(401, 'Unauthorized');
            }
        }

        $url = \DB::transaction(function () use ($post, $topic, $forum, $request) {
            // build url to post
            $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false);

            $notification = [
                'sender_id'   => $this->userId,
                'sender_name' => auth()->user()->name,
                'subject'     => excerpt($topic->subject, 48)
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
                if (is_null($topic->deleted_at)) {
                    $activity = Stream_Delete::class;
                    $redirect = redirect()->route('forum.category', [$forum->path]);

                    $subscribersId = $topic->subscribers()->lists('user_id');
                    if ($post->user_id !== null) {
                        $subscribersId[] = $post->user_id;
                    }

                    $topic->delete();
                    // delete topic's flag
                    app(FlagRepositoryInterface::class)->deleteBy('topic_id', $topic->id);

                    if ($subscribersId) {
                        app()->make('Alert\Topic\Delete')
                            ->with($notification)
                            ->setUrl($url)
                            ->setUsersId($subscribersId->toArray())
                            ->notify();
                    }

                    // fire the event. it can be used to delete row from "pages" table or from search index
                    event(new TopicWasDeleted($topic));
                } else {
                    $activity = Stream_Restore::class;
                    $topic->restore();

                    event(new TopicWasSaved($topic));
                    $redirect = redirect()->route('forum.topic', [$forum->path, $topic->id, $topic->path]);
                }

                $object = (new Stream_Topic())->map($topic, $forum);
                $target = (new Stream_Forum())->map($forum);
            } else {
                $url .= '?p=' . $post->id . '#id' . $post->id;

                if (is_null($post->deleted_at)) {
                    $activity = Stream_Delete::class;
                    $subscribersId = $post->subscribers()->lists('user_id');

                    if ($post->user_id !== null) {
                        $subscribersId[] = $post->user_id;
                    }

                    $post->delete();
                    // delete post's flags
                    app('FlagRepository')->deleteBy('post_id', $post->id);

                    if ($subscribersId) {
                        app()->make('Alert\Post\Delete')
                            ->with($notification)
                            ->setUrl($url)
                            ->setUsersId($subscribersId->toArray())
                            ->notify();
                    }

                    $redirect = back();
                    // fire the event. delete from search index
                    event(new PostWasDeleted($post));
                } else {
                    $activity = Stream_Restore::class;
                    $post->restore();
                    $redirect = redirect()->to($url);

                    // fire the event. add post to search engine
                    event(new PostWasSaved($post));
                }

                $object = (new Stream_Post(['url' => $url]))->map($post);
                $target = (new Stream_Topic())->map($topic, $forum);
            }

            if (!empty($reason)) {
                $object->reasonName = $reason->name;
            }

            stream($activity, $object, $target);
            return $redirect->with('success', 'Operacja zakończona sukcesem.');
        });

        return $url;
    }
}
