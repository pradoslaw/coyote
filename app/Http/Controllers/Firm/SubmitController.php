<?php

namespace Coyote\Http\Controllers\Firm;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\FilesystemFactory;
use Coyote\Http\Factories\ThumbnailFactory;
use Coyote\Services\Thumbnail\Objects\Logo;
use Illuminate\Http\Request;

class SubmitController extends Controller
{
    use FilesystemFactory, ThumbnailFactory;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logo(Request $request)
    {
        $this->validate($request, [
            'logo'             => 'required|image'
        ]);

        $fs = $this->getFilesystemFactory();
        $fileName = uniqid() . '.' . $request->file('logo')->getClientOriginalExtension();

        $path = config('filesystems.logo') . $fileName;
        $fs->put($path, file_get_contents($request->file('logo')->getRealPath()));

        $this->getThumbnailFactory()->setObject(new Logo())->make('storage/' . $path);

        return response()->json([
            'url' => asset('storage/' . $path),
            'name' => $fileName
        ]);
    }
}
