<?php
namespace Coyote\Http\Controllers\Adm;

use Carbon\Carbon;
use Coyote\Domain\Administrator\AvatarCdn;
use Coyote\Domain\Administrator\Report\EloquentStore;
use Coyote\Domain\Administrator\UserMaterial\Store\MaterialRequest;
use Coyote\Domain\Administrator\UserMaterial\Store\MaterialStore;
use Coyote\Domain\Administrator\UserMaterial\View\MarkdownRender;
use Coyote\Domain\Administrator\UserMaterial\View\MaterialVo;
use Coyote\Domain\Administrator\UserMaterial\View\Time;
use Coyote\Domain\View\Filter\Filter;
use Coyote\Domain\View\Pagination\BootstrapPagination;
use Coyote\Post;
use Illuminate\View\View;

class FlagController extends BaseController
{
    public function index(MaterialStore $store, MarkdownRender $render): View
    {
        $this->breadcrumb->push('Dodane treści', route('adm.flag'));

        $page = \max(1, (int)$this->request->query('page', 1));

        $paramFilterString = $this->queryOrNull('filter');
        $effectiveFilterString = $paramFilterString ?? 'type:post not:deleted';

        $filterParams = (new Filter($effectiveFilterString))->toArray();

        $materials = $store->fetch(new MaterialRequest(
            $page,
            10,
            $filterParams['type'] ?? 'post',
            $filterParams['deleted'] ?? null,
        ));

        return $this->view('adm.flag.home', [
            'materials'        => new MaterialVo($render, new Time(Carbon::now()), $materials, new AvatarCdn()),
            'pagination'       => new BootstrapPagination($page, 10, $materials->total, ['filter' => $paramFilterString]),
            'filter'           => $effectiveFilterString,
            'availableFilters' => ['type:post', 'type:comment', 'type:microblog', 'is:deleted', 'not:deleted'],
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

    private function queryOrNull(string $key): ?string
    {
        if ($this->request->query->has($key)) {
            return $this->request->query->get($key, '');
        }
        return null;
    }
}
