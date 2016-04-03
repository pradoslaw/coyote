<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PreferencesController extends Controller
{
    /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'tags.*' => 'max:25|tag',
            'city' => 'string',
            'salary' => 'int',
            'currency_id' => 'required|integer',
        ]);

        $this->setSetting('job.preferences', json_encode($request->except('_token')));
    }
}