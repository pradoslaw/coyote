<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Events\UserWasSaved;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\Forum\OrderRepositoryInterface as OrderRepository;
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
     * @var OrderRepository
     */
    private $order;

    /**
     * ForumController constructor.
     * @param ForumRepository $forum
     * @param OrderRepository $order
     */
    public function __construct(ForumRepository $forum, OrderRepository $order)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->order = $order;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Personalizacja forum', route('user.forum'));

        $this->forum->pushCriteria(new OnlyThoseWithAccess(auth()->user()));
        $sections = $this->forum->getOrderForUser($this->userId);

        return $this->view('user.forum')->with(compact('sections'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $this->validate($request, [
            'forum.*.is_hidden'       => 'int',
            'forum.*.order'           => 'int',
            'forum.*.section'         => 'string|max:50'
        ]);

        $this->order->saveForUser($this->userId, $request->input('forum'));
        event(new UserWasSaved($this->userId));
    }

    /**
     * Restore action
     */
    public function restore()
    {
        $this->order->deleteForUser($this->userId);
        event(new UserWasSaved($this->userId));
        
        return back();
    }
}
