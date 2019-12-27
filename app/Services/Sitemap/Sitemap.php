<?php

namespace Coyote\Services\Sitemap;

use Illuminate\Contracts\Filesystem\Filesystem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Sitemap
{
    const ROOT = 'sitemap';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $sites = [];

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        $this->createDirectory();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function response(Request $request)
    {
        return (new Response($this->read($request), 200))->header('Content-type', 'application/xml');
    }

    /**
     * Magic happens here: let's create sitemap.
     */
    public function save()
    {
        $index = new \SimpleXMLElement('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        // split links according to sitemap specification
        $chunk = array_chunk($this->sites, 49500);

        foreach ($chunk as $idx => $data) {
            $urlset = new \SimpleXMLElement('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

            foreach ($data as $row) {
                $url = $urlset->addChild('url');

                foreach ($row as $key => $value) {
                    $url->addChild($key, $value);
                }
            }

            $filename = 'sitemap' . ($idx + 1) . '.xml';
            $this->filesystem->put($this->path($filename), $urlset->asXML());

            $sitemap = $index->addChild('sitemap');
            $sitemap->addChild('loc', url(self::ROOT . '/' . $filename, [], true));
            $sitemap->addChild('lastmod', Carbon::now()->toIso8601String());
        }

        // saving sitemap index...
        $this->filesystem->put($this->path('sitemapindex.xml'), $index->asXML());
    }

    /**
     * @param Request $request
     * @return string
     */
    public function read(Request $request)
    {
        $filename = 'sitemapindex.xml';

        if (strpos($request->path(), '/') !== false) {
            list(, $filename) = explode('/', $request->path());
        }

        $path = self::ROOT . '/' . $filename;

        if (!$this->filesystem->exists($path)) {
            abort(404);
        }

        return $this->filesystem->get($path);
    }

    /**
     * @param string $url
     * @param string $dateTime
     * @return $this
     */
    public function add(string $url, string $dateTime)
    {
        $this->sites[] = [
            'loc' => $url,
            'lastmod' => $dateTime
        ];

        return $this;
    }

    /**
     * Create directory for sitemap in storage directory
     */
    protected function createDirectory()
    {
        if (!$this->filesystem->exists(self::ROOT)) {
            if (!$this->filesystem->makeDirectory(self::ROOT)) {
                throw new \Exception(sprintf('Cannot create storage %s directory.', self::ROOT));
            }
        }
    }

    /**
     * @param string $filename
     * @return string
     */
    protected function path($filename)
    {
        return self::ROOT . '/' . $filename;
    }
}
