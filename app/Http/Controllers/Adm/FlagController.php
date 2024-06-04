<?php
namespace Coyote\Http\Controllers\Adm;

use Carbon\Carbon;
use Coyote\Domain\Administrator\Report\EloquentStore;
use Coyote\Domain\Administrator\UserMaterial\Store\MaterialRequest;
use Coyote\Domain\Administrator\UserMaterial\Store\MaterialStore;
use Coyote\Domain\Administrator\UserMaterial\View\MarkdownRender;
use Coyote\Domain\Administrator\UserMaterial\View\MaterialVo;
use Coyote\Domain\Administrator\UserMaterial\View\Time;
use Coyote\Domain\View\Pagination\BootstrapPagination;
use Coyote\Post;
use Illuminate\View\View;

class FlagController extends BaseController
{
    public function index(MaterialStore $store, MarkdownRender $render): View
    {
        $this->breadcrumb->push('Dodane treści', route('adm.flag'));

        $page = \max(1, (int)$this->request->query('page', 1));
        $materials = $store->fetch(new MaterialRequest($page, 10, 'post'));

        return $this->view('adm.flag.home')->with([
            'materials'  => new MaterialVo($render, new Time(Carbon::now()), $materials),
            'pagination' => new BootstrapPagination($page, 10, $materials->total),
        ]);
    }

    public function show(Post $post, EloquentStore $store): View
    {
        $this->breadcrumb->push('Dodane treści', route('adm.flag'));
        $this->breadcrumb->push('#' . $post->id, route('adm.flag.show', [$post->id]));

        return $this->view('adm.flag.show')->with([
            'post'    => $store->reportedPostById($post->id),
            'reports' => $store->reportHistory($post->id),
            'backUrl' => route('adm.flag'),
        ]);
    }
}
