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
    public function getTags(): array
    {
        if (!($tags = $this->getRefererTags())) {
            $tags = $this->getUserPopularTags();
        }

        // filter only tags present in job offers
        return $this->filter($tags);
    }

    /**
     * @param array $tags
     * @return array
     */
    private function filter(array $tags): array
    {
        return $this->tag->whereIn('name', $tags)->join('job_tags', 'tag_id', '=', 'tags.id')->pluck('name')->toArray();
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

        return $page->tags;
    }

    /**
     * @return array
     */
    private function getUserPopularTags(): array
    {
        /** @var \Coyote\Guest $guest */
        $guest = $this->guest->find($this->request->session()->get('guest_id'));

        if (empty($guest) || empty($guest->interests)) {
            return [];
        }

        $ratio = $guest->interests['ratio'];
        arsort($ratio);

        // get only five top tags
        $ratio = array_slice($ratio, 0, 4);

        // only one tag please...
        return [array_rand($ratio)];
    }
}
