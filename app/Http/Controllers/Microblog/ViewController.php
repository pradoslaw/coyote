<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as Microblog;

class ViewController extends Controller
{
    /**
     * @param $id
     * @param Microblog $repository
     * @return \Illuminate\View\View
     */
    public function index($id, Microblog $repository)
    {
        /** @var \Coyote\Microblog $microblog */
        $microblog = $repository->findOrFail($id);
        abort_if(!is_null($microblog->parent_id), 404);

        $microblog->text = app('parser.microblog')->parse($microblog->text);
        $parser = app('parser.microblog.comment');

        foreach ($microblog->comments as &$comment) {
            $comment->html = $parser->parse($comment->text);
        }

        $excerpt = excerpt($microblog->text);

        $this->breadcrumb->push('Mikroblog', route('microblog.home'));
        $this->breadcrumb->push($excerpt, route('microblog.view', [$microblog->id]));

        return $this->view('microblog.view')->with(['microblog' => $microblog, 'excerpt' => $excerpt]);
    }
}
