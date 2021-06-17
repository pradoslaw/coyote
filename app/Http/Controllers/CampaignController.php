<?php

namespace Coyote\Http\Controllers;

use Coyote\Banner;

class CampaignController extends Controller
{
    public function redirect(Banner $banner)
    {
        $banner->increment('clicks');

        return redirect()->to($banner->url);
    }
}
