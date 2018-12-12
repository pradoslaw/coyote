<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Events\PostWasDeleted;
use Coyote\Events\PostWasSaved;
use Coyote\Services\Stream\Activities\Merge as Stream_Merge;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\UrlBuilder\UrlBuilder;

class MergeController extends BaseController
{
    /**
     * @param \Coyote\Post $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index($post)
    {
        $this->authorize('merge', $post->forum);

        /** @var \Coyote\Post $previous */
        $previous = $this->transaction(function () use ($post) {
            return $this->post->merge($this->userId, $post);
        });

        // add post to elasticsearch
        event(new PostWasSaved($previous));
        // remove from elasticsearch
        event(new PostWasDeleted($post));

        $url = UrlBuilder::topic($post->topic);

        $object = (new Stream_Post(['url' => $url]))->map($post);
        $target = (new Stream_Topic())->map($post->topic);

        stream(Stream_Merge::class, $object, $target);
        stream(Stream_Delete::class, $object, $target);

        $url .= '?p=' . $previous->id . '#id' . $previous->id;

        return redirect()->to($url)->with('success', 'Posty zostały połączone.');
    }
}
