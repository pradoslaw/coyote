<?php

namespace Coyote\Http\Controllers;

use Coyote\Http\Requests\MediaRequest;
use Coyote\Models\Media;
use Coyote\Services\Media\Clipboard;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Coyote\Services\Media\Factory as MediaFactory;
use Illuminate\Contracts\Filesystem\Filesystem;

class MediaController extends Controller
{
    public function upload(MediaRequest $request, Filesystem $filesystem)
    {
        $uploadedFile = $request->file('media');
        $path = $uploadedFile->store($this->userId);

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $media = Media::create([
            'name' => $uploadedFile->getClientOriginalName() !== 'blob' ? $uploadedFile->getClientOriginalName() : $this->getHumanName($extension),
            'path' => $path,
            'size' => $uploadedFile->getSize(),
            'mime' => $uploadedFile->getMimeType()
        ]);

        return array_merge($media->toArray(), ['url' => (string) $media->url]);
    }

    /**
     * @param string $extension
     * @return string
     */
    protected function getHumanName(string $extension)
    {
        return 'screenshot-' . date('YmdHis') . '.' . $extension;
    }
}
