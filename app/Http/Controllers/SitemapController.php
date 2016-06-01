<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;

class SitemapController extends Controller
{
    /**
     * @param PageRepository $page
     * @return \Illuminate\Http\Response
     */
    public function index(PageRepository $page)
    {
        $sitemap = $this->getSitemap();

        return $sitemap->remember(10, function ($sitemap) use ($page) {
            /** @var \Coyote\Services\Sitemap\Sitemap $sitemap */
            $page->forSitemap()->chunk(50000, function ($pages) use ($sitemap) {
                /** @var \Coyote\Page $page */
                foreach ($pages as $page) {
                    $priority = (1.0 - (count(explode('/', trim($page->path, '/'))) / 10));
                    $sitemap->add(url($page->path), $page->updated_at->toIso8601String(), sprintf('%.1f', $priority));
                }
            });
        });
    }

    /**
     * @return \Coyote\Services\Sitemap\Sitemap
     */
    private function getSitemap()
    {
        return app('sitemap');
    }
}
