<?php

namespace Coyote\Http\Controllers\Firm;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\MediaFactory;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    use MediaFactory;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $this->validate($request, [
            'photo'             => 'required|mimes:jpeg,jpg,png,gif'
        ]);

        $media = $this->getMediaFactory()->make('gallery')->upload($request->file('photo'));

        return response()->json([
            'url' => (string) $media->url()
        ]);
    }
}
