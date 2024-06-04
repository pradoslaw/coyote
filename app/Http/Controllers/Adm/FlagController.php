<?php
namespace Coyote\Http\Controllers\Adm;

use Coyote\Domain\Administrator\Report\EloquentStore;
use Coyote\Post;
use Illuminate\View\View;

class FlagController extends BaseController
{
    public function index(EloquentStore $store): View
    {
        $this->breadcrumb->push('Zgłoszone posty', route('adm.flag'));
        return $this->view('adm.flag.home')->with([
            'posts' => $store->reportedPosts(),
        ]);
    }

    public function show(Post $post, EloquentStore $store): View
    {
        $this->breadcrumb->push('Zgłoszone posty', route('adm.flag'));
        $this->breadcrumb->push('#' . $post->id, route('adm.flag.show', [$post->id]));

        return $this->view('adm.flag.show')->with([
            'post'    => $store->reportedPostById($post->id),
            'reports' => $store->reportHistory($post->id),
            'backUrl' => route('adm.flag'),
        ]);
    }
}
