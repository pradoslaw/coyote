<?php

namespace Coyote\Http\Controllers;

use Illuminate\Http\Request;

class SitemapController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sitemap = $this->getSitemap();

        return $sitemap->response($request);
    }

    /**
     * @return \Coyote\Services\Sitemap\Sitemap
     */
    private function getSitemap()
    {
        return app('sitemap');
    }
}
