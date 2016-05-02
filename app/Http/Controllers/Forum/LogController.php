<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\Post\LogRepositoryInterface;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Activities\Rollback as Stream_Rollback;
use Coyote\Post\Log;

class LogController extends BaseController
{
    /**
     * @var LogRepositoryInterface
     */
    private $log;

    /**
     * LogController constructor.
     * @param Forum $forum
     * @param Topic $topic
     * @param Post $post
     * @param LogRepositoryInterface $log
     */
    public function __construct(Forum $forum, Topic $topic, Post $post, LogRepositoryInterface $log)
    {
        parent::__construct($forum, $topic, $post);

        $this->log = $log;
    }

    /**
     * Show post history
     *
     * @param \Coyote\Post $post
     * @return mixed
     */
    public function log($post)
    {
        $topic = $this->topic->find($post->topic_id);
        $forum = $this->forum->find($post->forum_id);

        $this->authorize('update', $forum);

        $this->breadcrumb($forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->path, $topic->id, $topic->path]));
        $this->breadcrumb->push('Historia postu', route('forum.post.log', [$post->id]));

        $logs = $this->log->takeForPost($post->id);
        $parser = app()->make('Parser\Post');

        if (!request()->get('diff')) {
            foreach ($logs as &$log) {
                $log->text = $parser->parse($log->text);
            }
        } else {
            // @todo wyswietlanie diff
        }

        return $this->view('forum.log')->with(compact('logs', 'post', 'forum', 'topic'));
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
        $log = $this->log->findWhere(['id' => $logId, 'post_id' => $post->id])->first();

        $post->fill(['text' => $log->text, 'edit_count' => $post->edit_count + 1, 'editor_id' => $this->userId]);

        \DB::transaction(function () use ($post, $log, $topic, $forum) {
            $post->save();

            if ($post->id === $topic->first_post_id) {
                $topic->fill(['subject' => $log->subject]);
                // assign tags to topic
                $topic->tags()->sync(app('TagRepository')->multiInsert($log->tags));

                $topic->save();
            }

            $log = (new Log())->fill($log->toArray());
            $log->user_id = $this->userId;
            $log->save();

            $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false);

            stream(
                Stream_Rollback::class,
                (new Stream_Post(['url' => $url . '?p=' . $post->id . '#id' . $post->id]))->map($post),
                (new Stream_Topic())->map($topic, $forum)
            );
        });

        return redirect()->route('forum.topic', [$forum->path, $topic->id, $topic->path])->with('success', 'Post został przywrócony.');
    }
}
