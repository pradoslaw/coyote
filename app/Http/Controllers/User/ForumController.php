<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Repositories\Contracts\ForumRepositoryInterface;
use Coyote\Repositories\Contracts\Forum\OrderRepositoryInterface;
use Illuminate\Http\Request;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;

class ForumController extends BaseController
{
    use SettingsTrait;

    private $forum;
    private $order;
    
    public function __construct(ForumRepositoryInterface $forum, OrderRepositoryInterface $order)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->order = $order;
    }

    /**
     * @return $this
     */
    public function index()
    {
        $this->breadcrumb->push('Personalizacja forum', route('user.forum'));

        $groupsId = auth()->user()->groups()->lists('id')->toArray();

        $this->forum->pushCriteria(new OnlyThoseWithAccess($groupsId));
        $sections = $this->forum->groupBySections($this->userId, null);
        
        $this->order->takeForUser($this->userId);

        return $this->view('user.forum')->with(compact('sections'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        //
        return back()->with('success', 'Zmiany zostały poprawie zapisane');
    }
}
