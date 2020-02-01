<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Events\UserWasSaved;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Services\Forum\TreeBuilder;
use Illuminate\Http\Request;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;

class ForumController extends BaseController
{
    use SettingsTrait;

    /**
     * @var ForumRepository
     */
    private $forum;

    /**
     * @param ForumRepository $forum
     */
    public function __construct(ForumRepository $forum)
    {
        parent::__construct();

        $this->forum = $forum;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Personalizacja forum', route('user.forum'));

        $this->forum->pushCriteria(new OnlyThoseWithAccess(auth()->user()));
        $this->forum->pushCriteria(new AccordingToUserOrder($this->userId));

        $sections = $this->forum->categories($this->guestId);

        $treeBuilder = new TreeBuilder();

        $sections = $treeBuilder->sections($sections);

        return $this->view('user.forum')->with(compact('sections'));
    }

    /**
     * @param Request $request
     */
    public function save(Request $request)
    {
        $this->validate($request, [
            'forum.*.is_hidden'       => 'int',
            'forum.*.order'           => 'int',
            'forum.*.section'         => 'nullable|string|max:50'
        ]);

        $this->transaction(function () use ($request) {
            $this->forum->setup($this->userId, $request->input('forum'));

            event(new UserWasSaved($this->auth));
        });
    }

    /**
     * Restore action
     */
    public function restore()
    {
        $this->transaction(function () {
            $this->forum->deleteSetup($this->userId);

            event(new UserWasSaved($this->auth));
        });

        return back();
    }
}
