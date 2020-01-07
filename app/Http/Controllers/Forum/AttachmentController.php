<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Post\Attachment;
use Coyote\Repositories\Contracts\Post\AttachmentRepositoryInterface as AttachmentRepository;
use Coyote\Http\Controllers\AttachmentController as BaseAttachmentController;

class AttachmentController extends BaseAttachmentController
{
    /**
     * @var AttachmentRepository
     */
    private $attachment;

    /**
     * @param AttachmentRepository $attachment
     */
    public function __construct(AttachmentRepository $attachment)
    {
        parent::__construct();

        $this->attachment = $attachment;
    }

    /**
     * Download the attachment (or show image)
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     */
    public function download($id)
    {
        /** @var \Coyote\Post\Attachment $attachment */
        $attachment = $this->attachment->findOrFail($id);

        // post_id can be null if saving post was not completed.
        // post could also be deleted.
        if ($attachment->post === null) {
            abort(404);
        }

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
}
