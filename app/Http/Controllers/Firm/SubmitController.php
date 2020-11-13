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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function logo(Request $request)
    {
        $this->validate($request, [
            'logo'             => 'required|mimes:jpeg,jpg,png,gif'
        ]);

        $media = $this->getMediaFactory()->make('logo')->upload($request->file('logo'));

        return response()->json([
            'url'       => (string) $media->url()
        ]);
    }
}
