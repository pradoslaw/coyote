<?php

namespace Coyote\Services\Skills;

use Coyote\Repositories\Contracts\GuestRepositoryInterface as GuestRepository;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\User;
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

    protected ?User $user;

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
        $this->user = $this->request->user();
    }

    /**
     * @return array
     */
    public function getTags()
    {
        if ($tags = $this->refered()) {
            return $tags;
        }

//        if ($tags = $this->skills()) {
//            return $tags;
//        }

        return $this->popular();
    }

    /**
     * @return \Coyote\Tag[]|null
     */
    private function refered()
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
     * @return \Coyote\Tag[]|null
     */
    private function popular()
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
        return $result;
    }

    public function skills()
    {
        if (empty($this->user) || $this->user->skills) {
            return null;
        }

        $result = $this->tag->categorizedTags($this->user->skills->pluck('name')->toArray());

        if (!count($result)) {
            return null;
        }

        return $result;
    }
}
