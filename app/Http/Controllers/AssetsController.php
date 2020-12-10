<?php

namespace Coyote\Http\Controllers;

use Coyote\Http\Requests\AssetRequest;
use Coyote\Models\Asset;
use Coyote\Post;
use Coyote\Services\Media\Clipboard;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Coyote\Services\Media\Factory as MediaFactory;
use Illuminate\Contracts\Filesystem\Filesystem;

class AssetsController extends Controller
{
    public function upload(AssetRequest $request, Filesystem $filesystem)
    {
        $uploadedFile = $request->file('asset');
        $path = $uploadedFile->store($this->userId);

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $media = Asset::create([
            'name' => $uploadedFile->getClientOriginalName() !== 'blob' ? $uploadedFile->getClientOriginalName() : $this->getHumanName($extension),
            'path' => $path,
            'size' => $uploadedFile->getSize(),
            'mime' => $uploadedFile->getMimeType()
        ]);

        return array_merge($media->toArray(), ['url' => (string) $media->url]);
    }

    public function download(Asset $asset, string $name = null)
    {
        if ($asset->content_type === Post::class) {
            $this->authorize('access', $asset->post->forum);
        }

        set_time_limit(0);

        $asset->count = $asset->count + 1;
        $asset->save();

        return response()->download($asset->path, $name);
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
