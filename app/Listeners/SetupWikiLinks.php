<?php

namespace Coyote\Listeners;

use Coyote\Events\WikiWasDeleted;
use Coyote\Events\WikiWasSaved;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Services\Parser\Helpers\Link;
use Illuminate\Http\Request;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class SetupWikiLinks implements ShouldQueue
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var WikiRepository
     */
    protected $wiki;

    /**
     * @param Request $request
     * @param WikiRepository $wiki
     */
    public function __construct(Request $request, WikiRepository $wiki)
    {
        $this->request = $request;
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

        $links = $this->grabInternalLinks($cache);
        $result = [];

        foreach ($this->grabPathFromLink($links) as $path) {
            $result[] = $this->buildWikiLinkRecord($path, $event->wiki->id);
        }

        $event->wiki->links()->delete();

        foreach ($result as $link) {
            $event->wiki->links()->insert($link);
        }

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
     * @param string $html
     * @return Collection
     */
    private function grabInternalLinks($html)
    {
        $helper = new Link();
        $links = collect($helper->filter($html)); // convert array to collection

        return $links->filter(function ($url) {
            $host = parse_url($url, PHP_URL_HOST);

            return $this->request->getHost() === strtolower($host);
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
        $parts = explode('/', $path);
        $link = ['path_id' => $pathId];

        if ($parts[0] === 'Create') {
            array_shift($parts);

            $link['path'] = implode('/', $parts);
        } else {
            $page = $this->wiki->findByPath($path);

            $link['ref_id'] = $page->id;
            $link['path'] = $path;
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
