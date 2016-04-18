<?php

namespace Coyote\Http\Controllers\Firm;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\ThumbnailFactory;
use Coyote\Services\Thumbnail\Objects\Logo;
use Illuminate\Http\Request;

class SubmitController extends Controller
{
    use ThumbnailFactory;

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
            $path = public_path('storage/' . config('filesystems.logo'));

            $request->file('logo')->move($path, $fileName);
            $this->getThumbnailFactory()->setObject(new Logo())->make($path . $fileName);

            return response()->json([
                'url' => asset('storage/' . config('filesystems.logo') . $fileName),
                'name' => $fileName
            ]);
        }
    }
}
