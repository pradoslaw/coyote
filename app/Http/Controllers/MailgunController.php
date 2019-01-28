<?php

namespace Coyote\Http\Controllers;

use Illuminate\Http\Request;

class MailgunController extends Controller
{
    public function permanentFailure(Request $request)
    {
        logger()->debug($request->input());
    }
}
