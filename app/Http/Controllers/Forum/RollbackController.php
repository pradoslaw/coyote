<?php
namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Post;
use Coyote\Services\Stream\Activities\Rollback as Stream_Rollback;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\UrlBuilder;

class RollbackController extends Controller
{
    public function rollback(Post $post, int $revision): array
    {
        $this->authorize('update', $post->forum);

        $revision3 = $post->logs()->find($revision);
        $post->fill(['text' => $revision3->text, 'edit_count' => $post->edit_count + 1, 'editor_id' => $this->userId]);

        $this->transaction(function () use ($post, $revision3) {
            $post->save();

            if ($post->id === $post->topic->first_post_id) {
                // w starej wersji nie logowalismy zmian w temacie watku
                if ($revision3->title) {
                    $post->topic->fill(['title' => $revision3->title]);
                }
                if ($revision3->tags) {
                    $post->topic->setTags($revision3->tags);
                }
                $post->topic->save();
            }
            $post->save();
            stream(
                Stream_Rollback::class,
                (new Stream_Post(['url' => UrlBuilder::post($post)]))->map($post),
                (new Stream_Topic())->map($post->topic),
            );
        });

        session()->flash('success', 'Post został przywrócony.');

        return [
            'url' => UrlBuilder::post($post),
        ];
    }
}
