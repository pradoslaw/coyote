<?php

namespace Coyote\Http\Controllers;

use Coyote\Http\Factories\MediaFactory;
use Coyote\Http\Forms\AttachmentForm;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Illuminate\Http\Request;

abstract class AttachmentController extends Controller
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

        $media = $this->getMediaFactory()->make('attachment')->put(file_get_contents('data://' . substr($input, 7)));
        $mime = MimeTypeGuesser::getInstance();

        $attachment = $this->create([
            'size' => $media->size(),
            'mime' => $mime->guess($media->path()),
            'file' => $media->getFilename(),
            'name' => $media->getName()
        ]);

        return $this->renderForm($attachment);
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    abstract protected function create(array $attributes);

    /**
     * @param mixed $data
     * @return string
     */
    protected function renderForm($data)
    {
        $form = $this->createForm(AttachmentForm::class, $data);
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
