<?php

namespace Coyote\Http\Controllers;

use Coyote\Http\Requests\AssetRequest;
use Coyote\Models\Asset;
use Coyote\Post;
use Coyote\Services\Assets\Url;

class AssetsController extends Controller
{
    public function upload(AssetRequest $request)
    {
        $uploadedFile = $request->file('asset');
        $path = $uploadedFile->store($this->userId);

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $asset = Asset::create([
            'name' => $uploadedFile->getClientOriginalName() !== 'blob' ? $uploadedFile->getClientOriginalName() : $this->getHumanName($extension),
            'path' => $path,
            'size' => $uploadedFile->getSize(),
            'mime' => $uploadedFile->getMimeType()
        ]);

        return array_merge($asset->toArray(), ['url' => (string) Url::make($asset)]);
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
