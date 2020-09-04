<?php

namespace Coyote\Http\Controllers;

use Coyote\Http\Factories\MediaFactory;
use Coyote\Http\Forms\AttachmentForm;
use Coyote\Services\Media\Clipboard;
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

        return $this->render($attachment);
    }

    /**
     * @param Clipboard $clipboard
     * @return string
     */
    public function paste(Clipboard $clipboard)
    {
        $media = $clipboard->paste('attachment');

        $attachment = $this->create([
            'size' => $media->size(),
            'mime' => $media->getMime(),
            'file' => $media->getFilename(),
            'name' => $media->getName()
        ]);

        return $this->render($attachment);
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    abstract protected function create(array $attributes);

    abstract protected function render($attachment);

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
