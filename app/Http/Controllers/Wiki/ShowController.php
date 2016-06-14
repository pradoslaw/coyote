<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Factories\FlagFactory;
use Coyote\Http\Forms\Wiki\CommentForm;
use Coyote\Repositories\Criteria\Wiki\DirectAncestor;
use Coyote\Repositories\Criteria\Wiki\OnlyWithChildren;
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

        return $this->view('wiki.' . $wiki->template, [
            'wiki' => $wiki,
            'author' => $author,
            'categories' => $this->wiki->getAllCategories($wiki->id),
            'parents' => $this->parents->slice(1)->reverse(), // we skip current page
            'folders' => $this->getFolders($wiki->path_id),
            'children' => $this->getCatalog($wiki->path_id),
            'subscribed' => $wiki->subscribers()->forUser($this->userId)->exists(),
            'flag' => $this->getGateFactory()->allows('wiki-admin') ? $this->getFlagFactory()->takeForWiki($wiki->id) : '',
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
