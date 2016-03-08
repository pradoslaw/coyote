<?php

namespace Coyote\Http\Controllers\Firm;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Coyote\Thumbnail;

class SubmitController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logo(Request $request)
    {
        $this->validate($request, [
            'logo'             => 'required|image'
        ]);

        if ($request->file('logo')->isValid()) {
            $fileName = uniqid() . '.' . $request->file('logo')->getClientOriginalExtension();
            $path = public_path('storage/logo/');

            $request->file('logo')->move($path, $fileName);

            $thumbnail = new Thumbnail\Thumbnail(new Thumbnail\Objects\Logo());
            $thumbnail->make($path . $fileName);

            return response()->json([
                'url' => url('storage/logo/' . $fileName),
                'name' => $fileName
            ]);
        }
    }
}