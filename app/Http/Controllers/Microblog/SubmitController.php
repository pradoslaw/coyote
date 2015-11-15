<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Microblog;
use Illuminate\Http\Request;

class SubmitController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'text'          => 'required|string',
            'parent_id'     => 'sometimes|integer|exists:microblogs,id'
        ]);

        Microblog::create($request->all() + ['user_id' => auth()->user()->id]);
    }

    public function upload(Request $request)
    {
        $this->validate($request, [
            'photo'             => 'required|image'
        ]);

        if ($request->file('photo')->isValid()) {
            $fileName = uniqid() . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move(public_path() . '/storage/tmp/', $fileName);

            return response()->json([
                'url' => url('storage/tmp/' . $fileName),
                'name' => $fileName
            ]);
        }
    }
}
