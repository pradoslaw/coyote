<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Wiki\Attachment;
use Coyote\Http\Controllers\AttachmentController as BaseAttachmentController;

class AttachmentController extends BaseAttachmentController
{
    /**
     * @param \Coyote\Wiki $wiki
     * @param $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($wiki, $id)
    {
        /** @var \Coyote\Wiki\Attachment $attachment */
        $attachment = $wiki->attachments()->findOrFail($id);

        $headers = $this->getHeaders($attachment->name, $attachment->mime, false, $attachment->size);

        return response()->download(
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
