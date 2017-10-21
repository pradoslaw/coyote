<?php

namespace Coyote\Services\Skills;

use Coyote\Repositories\Contracts\GuestRepositoryInterface as GuestRepository;
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
     * @param Request $request
     * @param PageRepository $page
     * @param GuestRepository $guest
     */
    public function __construct(Request $request, PageRepository $page, GuestRepository $guest)
    {
        $this->request = $request;
        $this->page = $page;
        $this->guest = $guest;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        if (!($tags = $this->getRefererTags())) {
            $tags = $this->getUserPopularTags();
        }

        return $tags;
    }

    /**
     * @return array
     */
    private function getRefererTags(): array
    {
        $referer = filter_var($this->request->headers->get('referer'), FILTER_SANITIZE_URL);
        if (!$referer) {
            return [];
        }

        $path = parse_url($referer, PHP_URL_PATH);
        if (!$path) {
            return [];
        }

        $page = $this->page->findByPath($path);

        if (!$page || !$page->tags) {
            return [];
        }

        return array_combine($page->tags, array_fill(0, count($page->tags), 1));
    }

    /**
     * @return array
     */
    private function getUserPopularTags(): array
    {
        /** @var \Coyote\Guest $guest */
        $guest = $this->guest->find($this->request->session()->get('guest_id'));

        if (!$guest) {
            return [];
        }

        if (empty($guest->interests)) {
            return [];
        }

        $ratio = $guest->interests['ratio'];
        arsort($ratio);

        return array_slice($ratio, 0, 4);
    }
}
