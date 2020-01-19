<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Events\UserWasSaved;
use Coyote\Http\Factories\FlagFactory;
use Coyote\Repositories\Criteria\Topic\BelongsToForum;
use Coyote\Repositories\Criteria\Topic\StickyGoesFirst;
use Coyote\Services\Forum\TreeBuilder;
use Coyote\Services\Forum\Personalizer;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    use FlagFactory;

    /**
     * @param \Coyote\Forum $forum
     * @param Request $request
     * @param Personalizer $personalizer
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($forum, Request $request, Personalizer $personalizer)
    {
        $treeBuilder = new TreeBuilder();

        $this->pushForumCriteria();
        $forumList = $treeBuilder->listBySlug($this->forum->list());

        // execute query: get all subcategories that user can has access to
        $sections = $this->forum->categories($this->guestId, $forum->id);
        // mark unread categories
        $sections = $personalizer->markUnreadCategories($sections);

        $sections = $treeBuilder->sections($sections, $forum->id);

        // display topics for this category
        $this->topic->pushCriteria(new BelongsToForum($forum->id));
        $this->topic->pushCriteria(new StickyGoesFirst());
        // get topics according to given criteria
        $topics = $this
            ->topic
            ->lengthAwarePagination(
                $this->userId,
                $this->guestId,
                'topics.last_post_id',
                'DESC',
                $this->topicsPerPage($request)
            )
            ->appends($request->except('page'));

        $topics = $personalizer->markUnreadTopics($topics);
        $flags = [];

        // we need to get an information about flagged topics. that's how moderators can notice
        // that's something's wrong with posts.
        if ($topics->total() > 0 && $this->getGateFactory()->allows('delete', $forum)) {
            $flags = $this->getFlagFactory()->takeForTopics($topics->groupBy('id')->keys()->toArray());
        }

        $collapse = $this->collapse();
        $postsPerPage = $this->postsPerPage($this->request);

        return $this->view('forum.category')->with(
            compact('forumList', 'forum', 'topics', 'sections', 'collapse', 'flags', 'postsPerPage')
        );
    }

    /**
     * @param \Coyote\Forum $forum
     */
    public function mark($forum)
    {
        $forum->markAsRead($this->guestId);
        $this->topic->flushRead($forum->id, $this->guestId);

        $forums = $this->forum->where('parent_id', $forum->id)->get();

        foreach ($forums as $forum) {
            $forum->markAsRead($this->guestId);
            $this->topic->flushRead($forum->id, $this->guestId);
        }
    }

    /**
     * Set category visibility
     *
     * @param \Coyote\Forum $forum
     * @param Request $request
     */
    public function section($forum, Request $request)
    {
        $collapse = $this->getSetting('forum.collapse');
        if ($collapse !== null) {
            $collapse = unserialize($collapse);
        }

        $collapse[$forum->id] = (int) $request->input('flag');
        $this->setSetting('forum.collapse', serialize($collapse));
    }

    /**
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function order(Request $request)
    {
        $this->validate($request, ['forums.*.order' => 'required|int', 'forums.*.id' => 'required|int']);
        $this->pushForumCriteria();

        $categories = $this->forum->categories($this->guestId);
        $result = [];

        foreach ($categories as &$category) {
            foreach ($request->input('input') as $input) {
                if ($category->id === $input['id']) {
                    $category->order = $input['order'];
                }
            }

            $result[] = $category->only(['order']) + ['forum_id' => $category->id, 'is_hidden' => $category->is_hidden ?? false];
        }

        $this->transaction(function () use ($result) {
            $this->forum->saveOrder($this->userId, $result);
        });

        event(new UserWasSaved($this->auth));
    }
}
