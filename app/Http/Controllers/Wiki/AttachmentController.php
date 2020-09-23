<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Forms\AttachmentForm;
use Coyote\Wiki\Attachment;
use Coyote\Http\Controllers\AttachmentController as BaseAttachmentController;

class AttachmentController extends BaseAttachmentController
{
    /**
     * @param \Coyote\Wiki $wiki
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function download($wiki, $id)
    {
        /** @var \Coyote\Wiki\Attachment $attachment */
        $attachment = $wiki->attachments()->findOrFail($id);

        $headers = $this->getHeaders($attachment->name, $attachment->mime, false, $attachment->size);

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

    protected function render($attachment)
    {
        $form = $this->createForm(AttachmentForm::class, $attachment);
        // we're changing field name because front end expect this field to be an array
        $form->get('id')->setName('attachments[][id]');

        return (string) $form->render();
    }
}
