<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Factories\FlagFactory;
use Coyote\Http\Forms\Wiki\CommentForm;
use Coyote\Repositories\Criteria\Wiki\DirectAncestor;
use Coyote\Repositories\Criteria\Wiki\OnlyWithChildren;
use Coyote\Services\Elasticsearch\Factories\Wiki\MoreLikeThisFactory;
use Illuminate\Http\Request;

class ShowController extends BaseController
{
    use FlagFactory;

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \Coyote\Wiki $wiki */
        $wiki = $request->wiki;

        $author = $wiki->logs()->first()->user;
        $wiki->text = $this->getParser()->parse((string) $wiki->text);

        $parser = app('parser.comment');
        $wiki->load('comments.user');

        foreach ($wiki->comments as &$comment) {
            $comment->original_text = $comment->text;
            /** @var \Coyote\Wiki\Comment $comment */
            $comment->text = $parser->parse($comment->text);
        }

        $builder = (new MoreLikeThisFactory())->build($wiki);
        $build = $builder->build();

        return $this->view('wiki.' . $wiki->template, [
            'wiki' => $wiki,
            'author' => $author,
            'authors' => $wiki->authors()->get(),
            'categories' => $this->wiki->getAllCategories($wiki->wiki_id),
            'parents' => $this->parents->slice(1)->reverse(), // we skip current page
            'folders' => $this->getFolders($wiki->id),
            'children' => $this->getCatalog($wiki->id),
            'subscribed' => $wiki->subscribers()->forUser($this->userId)->exists(),
            'flag' => $this->getGateFactory()->allows('wiki-admin') ? $this->getFlagFactory()->takeForWiki($wiki->id) : '',
            'mlt' => $this->wiki->search($build)->getSource(),
            'form' => $this->createForm(CommentForm::class, [], [
                'url' => route('wiki.comment.save', [$wiki->id])
            ])
        ]);
    }

    /**
     * @param int $parentId
     * @return mixed
     */
    private function getFolders($parentId)
    {
        $this->wiki->pushCriteria(new OnlyWithChildren());
        $this->wiki->pushCriteria(new DirectAncestor($parentId));

        $result = $this->wiki->children($parentId);
        $this->wiki->resetCriteria();

        return $result;
    }

    /**
     * @param int $parentId
     * @return mixed
     */
    private function getCatalog($parentId)
    {
        return $this->wiki->getCatalog($parentId);
    }
}
