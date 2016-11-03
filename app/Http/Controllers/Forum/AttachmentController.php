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
        $attachment->post->forum->userCanAccess($this->userId) || abort(401, 'Unauthorized');

        set_time_limit(0);

        $attachment->count = $attachment->count + 1;
        $attachment->save();

        $isImage = $attachment->file->isImage();

        $headers = [
            'Content-Type' => $attachment->mime,
            'Content-Disposition' => (!$isImage ? 'attachment' : 'inline') . '; filename="' . $attachment->name . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Length' => $attachment->size,
            'Cache-control' => 'private',
            'Pragma' => 'private',
            'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT'
        ];

        return $isImage ? response()->make(
            $attachment->file->get(),
            200,
            $headers
        ) : response()->download(
            $attachment->file->path(),
            $attachment->name,
            $headers
        );
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
