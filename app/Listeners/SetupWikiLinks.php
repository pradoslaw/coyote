<?php

namespace Coyote\Listeners;

use Coyote\Events\WikiWasDeleted;
use Coyote\Events\WikiWasSaved;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Services\Parser\Helpers\Link;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class SetupWikiLinks implements ShouldQueue
{
    /**
     * @var WikiRepository
     */
    protected $wiki;

    /**
     * @param WikiRepository $wiki
     */
    public function __construct(WikiRepository $wiki)
    {
        $this->wiki = $wiki;
    }

    /**
     * Handle the event.
     *
     * @param  WikiWasSaved  $event
     * @return void
     */
    public function onWikiSave(WikiWasSaved $event)
    {
        // at this point, text is probably in cache, so we need to just read from it.
        $cache = $this->getParser()->parse($event->wiki->text);

        // step 1. grab only internal links
        $links = $this->grabInternalLinks($event->host, $cache);
        $result = [];

        // step 2. get only path from links and build record to insert
        foreach ($this->grabPathFromLink($links) as $path) {
            if (!validator(['path' => trim($path, '/')], ['path' => 'wiki_route'])->fails()) {
                $result[] = $this->buildWikiLinkRecord($path, $event->wiki->id);
            }
        }

        // step 3. remove all current links
        $event->wiki->links()->delete();

        // step 4. insert the new ones
        foreach ($result as $link) {
            $event->wiki->links()->insert($link);
        }

        // step 5. purge cache from all articles which are connected to this one.
        foreach ($this->wiki->getWikiAssociatedLinksByPath($event->wiki->path) as $row) {
            $this->getParser()->purgeFromCache($row->text);
        }

        $this->wiki->associateLink($event->wiki->path, $event->wiki->id);
    }

    /**
     * @param WikiWasDeleted $event
     */
    public function onWikiDelete(WikiWasDeleted $event)
    {
        $this->getParser()->purgeFromCache($event->wiki['text']);
        $links = $this->wiki->getWikiAssociatedLinksByPath($event->wiki['path']);

        $this->wiki->dissociateLink($event->wiki['path']);

        foreach ($links as $row) {
            $this->getParser()->purgeFromCache($row['text']);
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Coyote\Events\WikiWasSaved',
            'Coyote\Listeners\SetupWikiLinks@onWikiSave'
        );

        $events->listen(
            'Coyote\Events\WikiWasDeleted',
            'Coyote\Listeners\SetupWikiLinks@onWikiDelete'
        );
    }

    /**
     * @param string $host
     * @param string $html
     * @return Collection
     */
    private function grabInternalLinks($host, $html)
    {
        $helper = new Link();
        $links = collect($helper->filter($html)); // convert array to collection

        return $links->filter(function ($url) use ($host) {
            return $host === strtolower(parse_url($url, PHP_URL_HOST));
        });
    }

    /**
     * @param Collection $links
     * @return Collection
     */
    private function grabPathFromLink($links)
    {
        return $links->map(function ($url) {
            return trim(parse_url($url, PHP_URL_PATH), '/');
        });
    }

    /**
     * @param string $path
     * @param int $pathId
     * @return array
     */
    private function buildWikiLinkRecord($path, $pathId)
    {
        $path = urldecode($path);

        $parts = explode('/', $path);
        $link = ['path_id' => $pathId, 'ref_id' => null];

        if ($parts[0] === 'Create') {
            array_shift($parts);

            $link['path'] = implode('/', $parts);
        } else {
            $link['path'] = $path;
            $page = $this->wiki->findByPath($path);

            if ($page) {
                $link['ref_id'] = $page->id;
            }
        }

        return $link;
    }

    /**
     * @return \Coyote\Services\Parser\Factories\AbstractFactory
     */
    protected function getParser()
    {
        return app('parser.wiki');
    }
}
