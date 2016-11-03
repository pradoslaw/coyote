<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Factories\MediaFactory;
use Coyote\Http\Forms\AttachmentForm;
use Coyote\Wiki\Attachment;
use Illuminate\Http\Request;
use Coyote\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

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

        $media = $this->getMediaFactory('attachment')->upload($request->file('attachment'));
        $mime = MimeTypeGuesser::getInstance();

        $attachment = (new Attachment)->create([
            'size' => $media->size(),
            'file' => $media->getFilename(),
            'name' => $media->getName(),
            'mime' => $mime->guess($media->path())
        ]);

        return $this->renderForm($attachment);
    }

    /**
     * Paste image from clipboard
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function paste()
    {
        $input = file_get_contents("php://input");

        $validator = $this->getValidationFactory()->make(
            ['length' => strlen($input)],
            ['length' => 'max:' . config('filesystems.upload_max_size') * 1024 * 1024]
        );

        $this->validateWith($validator);

        $media = $this->getMediaFactory('attachment')->put(file_get_contents('data://' . substr($input, 7)));
        $mime = MimeTypeGuesser::getInstance();

        $attachment = (new Attachment)->create([
            'size' => $media->size(),
            'mime' => $mime->guess($media->path()),
            'file' => $media->getFilename(),
            'name' => $media->getName()
        ]);

        return $this->renderForm($attachment);
    }

    /**
     * @param Attachment $data
     * @return string
     */
    protected function renderForm($data)
    {
        $form = $this->createForm(AttachmentForm::class, $data);
        // we're changing field name because front end expect this field to be an array
        $form->get('id')->setName('attachments[][id]');

        return $form->render();
    }
}
