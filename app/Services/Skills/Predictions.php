<?php

namespace Coyote\Services\Skills;

use Coyote\Repositories\Contracts\GuestRepositoryInterface as GuestRepository;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Illuminate\Http\Request;
use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;

class Predictions
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var PageRepository
     */
    protected $page;

    /**
     * @var GuestRepository
     */
    protected $guest;

    /**
     * @var TagRepository
     */
    protected $tag;

    /**
     * @param Request $request
     * @param PageRepository $page
     * @param GuestRepository $guest
     * @param TagRepository $tag
     */
    public function __construct(Request $request, PageRepository $page, GuestRepository $guest, TagRepository $tag)
    {
        $this->request = $request;
        $this->page = $page;
        $this->guest = $guest;
        $this->tag = $tag;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        if (!($tags = $this->getRefererTags())) {
            $tags = $this->getUserPopularTags();
        }

        return $tags;
    }

    /**
     * @return \Coyote\Tag[]|null
     */
    private function getRefererTags()
    {
        $referer = filter_var($this->request->headers->get('referer'), FILTER_SANITIZE_URL);
        if (!$referer) {
            return null;
        }

        $path = parse_url(urldecode($referer), PHP_URL_PATH);
        if (!$path) {
            return null;
        }

        $page = $this->page->findByPath($path);

        if (!$page || !$page->tags) {
            return null;
        }

        $result = $this->tag->categorizedTags($page->tags);

        return count($result) ? $result : null;
    }

    /**
     * @return null|\Illuminate\Support\Collection
     */
    private function getUserPopularTags()
    {
        /** @var \Coyote\Guest $guest */
        $guest = $this->guest->find($this->request->session()->get('guest_id'));

        if (empty($guest) || empty($guest->interests)) {
            return null;
        }

        $ratio = $guest->interests['ratio'];
        arsort($ratio);

        // get only five top tags
        $result = $this->tag->categorizedTags(array_slice(array_keys($ratio), 0, 4));

        if (!count($result)) {
            return null;
        }

        // only one tag please...
        return collect()->push($result->random());
    }
}
