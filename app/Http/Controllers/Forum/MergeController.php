<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Events\PostWasDeleted;
use Coyote\Events\PostWasSaved;
use Coyote\Services\Stream\Activities\Merge as Stream_Merge;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;

class MergeController extends BaseController
{
    /**
     * @param \Coyote\Post $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index($post)
    {
        $forum = $post->forum()->first();
        $this->authorize('merge', $forum);

        $url = $this->transaction(function () use ($post, $forum) {
            /** @var \Coyote\Post $previous */
            $previous = $post->previous();

            $text = $previous->text . "\n\n" . $post->text;

            /** @var \Coyote\Topic $topic */
            $topic = $post->topic()->first();

            $previous->update(['text' => $text, 'edit_count' => $previous->edit_count + 1, 'editor_id' => $this->userId]);
            $previous->logs()->create(
                array_merge($post->toArray(), ['text' => $text, 'subject' => $topic->subject, 'tags' => []])
            );

            $post->delete();

            // add post to elasticsearch
            event(new PostWasSaved($previous));
            // remove from elasticsearch
            event(new PostWasDeleted($post));

            stream(
                Stream_Merge::class,
                (new Stream_Post())->map($post),
                (new Stream_Topic())->map($topic, $forum)
            );

            return route('forum.topic',  [$forum->slug, $topic->id, $topic->slug]) . '?p=' . $previous->id . '#id' . $previous->id;
        });

        return redirect()->to($url);
    }
}
