<?php

namespace Coyote\Http\Controllers\Firm;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\MediaFactory;
use Illuminate\Http\Request;

class SubmitController extends Controller
{
    use MediaFactory;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logo(Request $request)
    {
        $this->validate($request, [
            'logo'             => 'required|image'
        ]);

        $media = $this->getMediaFactory('logo')->upload($request->file('logo'));

        return response()->json([
            'url' => $media->url(),
            'name' => $media->getFilename()
        ]);
    }
}
