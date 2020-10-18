<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Resources\PostAttachmentResource;
use Coyote\Post\Attachment;
use Coyote\Http\Controllers\AttachmentController as BaseAttachmentController;

class AttachmentController extends BaseAttachmentController
{
    /**
     * @param Attachment $attachment
     * @return \Illuminate\Http\Response|mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function download(Attachment $attachment)
    {
        // post_id can be null if saving post was not completed.
        // post could also be deleted.
        abort_if($attachment->post === null, 404);

        $this->authorize('access', $attachment->post->forum);

        set_time_limit(0);

        $attachment->count = $attachment->count + 1;
        $attachment->save();

        $isImage = $attachment->file->isImage();
        $headers = $this->getHeaders($attachment->name, $attachment->mime, $isImage, $attachment->size);

        return response()->make($attachment->file->get(), 200, $headers);
    }

    /**
     * @param array $attributes
     * @return Attachment
     */
    protected function create(array $attributes)
    {
        return Attachment::create($attributes);
    }

    /**
     * @param Attachment $attachment
     * @return PostAttachmentResource
     */
    protected function render($attachment)
    {
        PostAttachmentResource::withoutWrapping();

        return new PostAttachmentResource($attachment);
    }
}
