<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;

class ViewController extends Controller
{
    /**
     * @param $id
     * @param MicroblogRepository $repository
     * @return \Illuminate\View\View
     */
    public function index($id, MicroblogRepository $repository)
    {
        /** @var \Coyote\Microblog $microblog */
        $microblog = $repository->findOrFail($id);
        abort_if(!is_null($microblog->parent_id), 404);

        $excerpt = excerpt($microblog->html);

        $this->breadcrumb->push('Mikroblog', route('microblog.home'));
        $this->breadcrumb->push($excerpt, route('microblog.view', [$microblog->id]));

        return $this->view('microblog.view')->with(['microblog' => $microblog, 'excerpt' => $excerpt]);
    }
}
