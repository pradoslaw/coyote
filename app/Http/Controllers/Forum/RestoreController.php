<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Services\Stream\Activities\Restore as Stream_Restore;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Forum as Stream_Forum;
use Coyote\Events\PostWasSaved;
use Coyote\Events\TopicWasSaved;
use Coyote\Services\UrlBuilder;

class RestoreController extends BaseController
{
    /**
     * Restore post or whole topic
     *
     * @param int $id post id
     */
    public function index(int $id)
    {
        // Step 1. Does post really exist?
        /** @var \Coyote\Post $post */
        $post = $this->post->withTrashed()->findOrFail($id);

        $this->authorize('delete', [$post, $post->forum]);
        $this->authorize('access', [$post->forum]);

        $url = UrlBuilder::topic($post->topic);

        if ($post->id === $post->topic->first_post_id) {
            $post->topic->restore();

            event(new TopicWasSaved($post->topic));

            $object = (new Stream_Topic())->map($post->topic, $post->forum);
            $target = (new Stream_Forum())->map($post->forum);
        } else {
            $url .= '?p=' . $post->id . '#id' . $post->id;
            $post->restore();

            // fire the event. add post to search engine
            event(new PostWasSaved($post));

            $object = (new Stream_Post(['url' => $url]))->map($post);
            $target = (new Stream_Topic())->map($post->topic);
        }

        stream(Stream_Restore::class, $object, $target);
    }
}
