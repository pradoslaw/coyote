<?php

namespace Coyote\Services\Sitemap;

use Illuminate\Contracts\Filesystem\Filesystem;
use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Sitemap
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $root;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var array
     */
    protected $sites = [];

    /**
     * @param Filesystem $filesystem
     * @param Request $request
     */
    public function __construct(Filesystem $filesystem, Request $request)
    {
        $this->filesystem = $filesystem;

        $this->root = $request->path();
        $this->filename = 'sitemapindex.xml';

        if (strpos($request->path(), '/') !== false) {
            list($this->root, $this->filename) = explode('/', $request->path());
        }

        $this->createDirectory();
    }

    /**
     * @param int $minutes
     * @param Closure $closure
     * @return Response
     */
    public function remember($minutes, Closure $closure)
    {
        if ($this->hasCacheExpired($minutes)) {
            $closure($this);
            $this->create();
        }

        return (new Response($this->read(), 200))->header('Content-type', 'application/xml');
    }

    /**
     * Magic happens here: let's create sitemap.
     */
    protected function create()
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
            $sitemap->addChild('loc', url($this->root . '/' . $filename));
            $sitemap->addChild('lastmod', Carbon::now()->toIso8601String());
        }

        // saving sitemap index...
        $this->filesystem->put($this->path('sitemapindex.xml'), $index->asXML());
    }

    /**
     * @return string
     */
    public function read()
    {
        return $this->filesystem->get($this->root . '/' . $this->filename);
    }

    /**
     * @param string $url
     * @param string $dateTime
     * @param float $priority
     * @return $this
     */
    public function add($url, $dateTime, $priority)
    {
        $this->sites[] = [
            'loc' => $url,
            'lastmod' => $dateTime,
            'priority' => $priority
        ];

        return $this;
    }

    /**
     * Create directory for sitemap in storage directory
     */
    protected function createDirectory()
    {
        if (!$this->filesystem->exists($this->root)) {
            if (!$this->filesystem->makeDirectory($this->root)) {
                throw new \Exception(sprintf('Cannot create storage %s directory.', $this->root));
            }
        }
    }

    /**
     * @param string $filename
     * @return string
     */
    protected function path($filename)
    {
        return $this->root . '/' . $filename;
    }

    /**
     * @param int $minutes
     * @return bool
     */
    protected function hasCacheExpired($minutes)
    {
        $path = $this->path($this->filename);

        return $this->filesystem->exists($path)
            ? (time() - $this->filesystem->lastModified($path)) / 60 > $minutes
            : true;
    }
}
