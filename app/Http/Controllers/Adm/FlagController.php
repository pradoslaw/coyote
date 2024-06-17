<?php
namespace Coyote\Http\Controllers\Adm;

use Carbon\Carbon;
use Coyote\Domain\Administrator\AvatarCdn;
use Coyote\Domain\Administrator\UserMaterial\List\Store\MaterialRequest;
use Coyote\Domain\Administrator\UserMaterial\List\Store\MaterialStore;
use Coyote\Domain\Administrator\UserMaterial\List\View\MarkdownRender;
use Coyote\Domain\Administrator\UserMaterial\List\View\MaterialVo;
use Coyote\Domain\Administrator\UserMaterial\List\View\Time;
use Coyote\Domain\Administrator\UserMaterial\Show\PostMaterialPresenter;
use Coyote\Domain\Administrator\UserMaterial\Show\View\CommentMaterial;
use Coyote\Domain\Administrator\View\Mention;
use Coyote\Domain\View\Filter\Filter;
use Coyote\Domain\View\Pagination\BootstrapPagination;
use Coyote\Post;
use Coyote\Services\UrlBuilder;
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
            $filterParams['reported'] ?? null,
            $filterParams['author'] ?? null,
        ));

        return $this->view('adm.flag.home', [
            'materials'        => new MaterialVo($render, new Time(Carbon::now()), $materials, new AvatarCdn()),
            'pagination'       => new BootstrapPagination($page, 10, $materials->total, ['filter' => $paramFilterString]),
            'filter'           => $effectiveFilterString,
            'availableFilters' => [
                'type:post', 'type:comment', 'type:microblog',
                'is:deleted', 'not:deleted',
                'is:reported', 'not:reported',
                'author:{id}',
            ],
        ]);
    }

    public function showPost(Post $post, PostMaterialPresenter $presenter): View
    {
        $this->breadcrumb->push('Dodane treści', route('adm.flag'));
        $this->breadcrumb->push('Post #' . $post->id, route('adm.flag.show.post', [$post->id]));

        return $this->view('adm.flag.show.post')->with([
            'post'    => $presenter->post($post->id),
            'backUrl' => route('adm.flag'),
        ]);
    }

    public function showComment(Post\Comment $comment, Time $time): View
    {
        $this->breadcrumb->push('Dodane treści', route('adm.flag'));
        $this->breadcrumb->push('Komentarz #' . $comment->id, route('adm.flag.show.comment', [$comment->id]));

        return $this->view('adm.flag.show.comment')->with([
            'comment' => new CommentMaterial(
                $comment->text,
                $comment->user_id,
                new Mention($comment->user_id, $comment->user->name),
                $time->date($comment->created_at),
                UrlBuilder::postComment($comment),
            ),
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
