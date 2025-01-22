<?php

namespace Coyote\Services\Skills;

use Coyote\Guest;
use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Tag;
use Coyote\User;
use Illuminate\Http\Request;

class Predictions
{
    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var PageRepository
     */
    protected PageRepository $page;

    /**
     * @var TagRepository
     */
    protected TagRepository $tag;

    protected ?User $user;

    /**
     * @param Request $request
     * @param PageRepository $page
     * @param TagRepository $tag
     */
    public function __construct(Request $request, PageRepository $page, TagRepository $tag)
    {
        $this->request = $request;
        $this->page = $page;
        $this->tag = $tag;
        $this->user = $this->request->user();
    }

    /**
     * @return array|null
     */
    public function getTags()
    {
        if ($tags = $this->refered()) {
            return $tags;
        }

        if ($tags = $this->skills()) {
            return $tags;
        }

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

        return $this->tag->categorizedTags($page->tags) ?? null;
    }

    /**
     * @return \Coyote\Tag[]|null
     */
    private function popular()
    {
        /** @var \Coyote\Guest $guest */
        $guest = Guest::find($this->request->session()->get('guest_id'));

        if (empty($guest) || empty($guest->interests)) {
            return null;
        }

        $ratio = $guest->interests['ratio'];
        arsort($ratio);

        return $this->tag->categorizedTags(array_slice(array_keys($ratio), 0, 4)) ?? null;
    }

    public function skills()
    {
        if (empty($this->user) || !$this->user->skills) {
            return null;
        }

        return $this->tag->categorizedTags($this->user->skills->pluck('name')->toArray()) ?? null;
    }
}
