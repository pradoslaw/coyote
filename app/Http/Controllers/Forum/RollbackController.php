<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Post;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Activities\Rollback as Stream_Rollback;
use Coyote\Services\UrlBuilder;

class RollbackController extends Controller
{
    public function rollback(Post $post, int $revision)
    {
        $this->authorize('update', $post->forum);

        $revision = $post->logs()->find($revision);
        $post->fill(['text' => $revision->text, 'edit_count' => $post->edit_count + 1, 'editor_id' => $this->userId]);

        $this->transaction(function () use ($post, $revision) {
            $post->save();

            if ($post->id === $post->topic->first_post_id) {
                // w starej wersji nie logowalismy zmian w temacie watku
                if ($revision->title) {
                    $post->topic->fill(['title' => $revision->title]);
                }

                if ($revision->tags) {
                    // assign tags to topic
                    $post->topic->setTags($revision->tags);
                }

                $post->topic->save();
            }

            $post->save();

            $url = UrlBuilder::post($post);

            stream(
                Stream_Rollback::class,
                (new Stream_Post(['url' => $url]))->map($post),
                (new Stream_Topic())->map($post->topic)
            );
        });

        session()->put('success', 'Post został przywrócony.');

        return [
            'url' => UrlBuilder::topic($post->topic)
        ];
    }
}
