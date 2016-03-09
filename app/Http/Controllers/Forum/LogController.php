<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\Post\LogRepositoryInterface as Log;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;

class LogController extends BaseController
{
    private $log;

    public function __construct(Forum $forum, Topic $topic, Log $log)
    {
        parent::__construct($forum, $topic);

        $this->log = $log;
    }

    /**
     * Show post history
     *
     * @param \Coyote\Post $post
     * @param Log $log
     * @return mixed
     */
    public function log($post, Log $log)
    {
        $topic = $this->topic->find($post->topic_id);
        $forum = $this->forum->find($post->forum_id);

        $this->authorize('update', $forum);

        $this->breadcrumb($forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->path, $topic->id, $topic->path]));
        $this->breadcrumb->push('Historia postu', route('forum.post.log', [$post->id]));

        $logs = $log->takeForPost($post->id);

        return $this->view('forum.post.log')->with(compact('logs', 'post', 'forum', 'topic'));
    }

    /**
     * Rollback post to $logId version
     *
     * @param \Coyote\Post $post
     * @param int $logId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rollback($post, $logId)
    {
        $forum = $this->forum->find($post->forum_id);
        $this->authorize('update', $forum);

        $topic = $this->topic->find($post->topic_id);
        $log = $this->log->where('id', $logId)->where('post_id', $post->id)->firstOrFail();

        $post->fill(['text' => $log->text, 'edit_count' => $post->edit_count + 1, 'editor_id' => $this->userId]);

        \DB::transaction(function () use ($post, $log, $topic) {
            $post->save();

            if ($post->id === $topic->first_post_id) {
                $path = str_slug($log->subject, '_');
                $topic->fill(['subject' => $log->subject, 'path' => $path]);

                $this->topic->setTags($topic->id, $log->tags);
            }

            $this->log->add($post->id, $this->userId, $log->text, $log->subject, $log->tags);
        });

        return redirect()->route('forum.topic', [$forum->path, $topic->id, $topic->path])->with('success', 'Post został przywrócony.');
    }
}
