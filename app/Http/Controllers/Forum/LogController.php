<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\Post\LogRepositoryInterface as LogRepository;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\TagRepositoryInterface;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Activities\Rollback as Stream_Rollback;
use Coyote\Post\Log;
use Coyote\Services\UrlBuilder\UrlBuilder;

class LogController extends BaseController
{
    /**
     * @var LogRepository
     */
    private $log;

    /**
     * LogController constructor.
     * @param Forum $forum
     * @param Topic $topic
     * @param Post $post
     * @param LogRepository $log
     */
    public function __construct(Forum $forum, Topic $topic, Post $post, LogRepository $log)
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
        $topic = $post->topic;
        $forum = $topic->forum;

        $this->authorize('update', $forum);

        $this->breadcrumb($forum);
        $this->breadcrumb->push([
            $topic->subject => route('forum.topic', [$forum->slug, $topic->id, $topic->slug]),
            'Historia postu' => route('forum.post.log', [$post->id])
        ]);

        $logs = $this->log->takeForPost($post->id);

        $raw = $logs->pluck('text')->toJson();

        /** @var \Coyote\Services\Parser\Factories\AbstractFactory $parser */
        $parser = app('parser.post');
        $parser->cache->setEnable(false);

        foreach ($logs as &$log) {
            $log->text = $parser->parse($log->text);
        }

        return $this->view('forum.log')->with(compact('logs', 'post', 'forum', 'topic', 'raw'));
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
        $this->authorize('update', $post->forum);

        $topic = $post->topic;
        $log = $this->log->findWhere(['id' => $logId, 'post_id' => $post->id])->first();

        $post->fill(['text' => $log->text, 'edit_count' => $post->edit_count + 1, 'editor_id' => $this->userId]);

        $this->transaction(function () use ($post, $log, $topic) {
            $post->save();

            if ($post->id === $topic->first_post_id) {
                // w starej wersji nie logowalismy zmian w temacie watku
                if ($log->subject) {
                    $topic->fill(['subject' => $log->subject]);
                }

                if ($log->tags) {
                    // assign tags to topic
                    $topic->tags()->sync(app(TagRepositoryInterface::class)->multiInsert($log->tags));
                }

                $topic->save();
            }

            $log = (new Log())->fill($log->toArray());
            $log->user_id = $this->userId;
            $log->ip = $this->request->ip();
            $log->browser = $this->request->browser();
            $log->host = $this->request->getClientHost();

            $log->save();

            $url = UrlBuilder::post($post);

            stream(
                Stream_Rollback::class,
                (new Stream_Post(['url' => $url]))->map($post),
                (new Stream_Topic())->map($topic)
            );
        });

        return redirect()
            ->to(UrlBuilder::topic($topic))
            ->with('success', 'Post został przywrócony.');
    }
}
