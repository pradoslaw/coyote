<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Http\Forms\AttachmentForm;
use Coyote\Wiki\Attachment;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    use MediaFactory;

    /**
     * Upload file to server
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function upload(Request $request)
    {
        $this->validate($request, ['attachment' => sprintf(
            'required|max:%s|mimes:%s',
            config('filesystems.upload_max_size') * 1024,
            config('filesystems.upload_mimes')
        )]);

        $media = $this->getMediaFactory()->make('attachment')->upload($request->file('attachment'));

        $attachment = $this->create([
            'size' => $media->size(),
            'file' => $media->getFilename(),
            'name' => $media->getName(),
            'mime' => $media->getMime()
        ]);

        return $this->render($attachment);
    }

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

    /**
     * @param string $name
     * @param string $mime
     * @param bool $image
     * @param int $size
     * @return array
     */
    protected function getHeaders(string $name, string $mime, bool $image, int $size)
    {
        return [
            'Content-Type' => $mime,
            'Content-Disposition' => (!$image ? 'attachment' : 'inline') . '; filename="' . $name . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Length' => $size,
            'Cache-control' => 'private',
            'Pragma' => 'private',
            'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT'
        ];
    }
}
