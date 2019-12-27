<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Factories\FlagFactory;
use Coyote\Http\Forms\Wiki\CommentForm;
use Coyote\Repositories\Criteria\Wiki\DirectAncestor;
use Coyote\Repositories\Criteria\Wiki\OnlyWithChildren;
use Coyote\Services\Elasticsearch\Builders\Wiki\MoreLikeThisBuilder;
use Coyote\Wiki;
use Illuminate\Http\Request;

class ShowController extends BaseController
{
    use FlagFactory, CacheFactory;

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \Coyote\Wiki $wiki */
        $wiki = $request->attributes->get('wiki');

        $author = $wiki->logs()->exists() ? $wiki->logs()->orderBy('id')->first()->user : null;
        $wiki->text = $this->getParser()->parse((string) $wiki->text);

        $parser = app('parser.wiki');
        $wiki->load(['comments' => function ($query) {
            return $query->with(['user' => function ($query) {
                return $query->withTrashed();
            }]);
        }]);

        foreach ($wiki->comments as &$comment) {
            /** @var \Coyote\Wiki\Comment $comment */
            $comment->html = $parser->parse($comment->text);
        }

        $view = $this->view('wiki.' . $wiki->template, [
            'wiki' => $wiki,
            'author' => $author,
            'authors' => $wiki->authors()->get(),
            'categories' => $this->wiki->getAllCategories($wiki->wiki_id),
            'parents' => $this->parents->slice(1)->reverse(), // we skip current page
            'subscribed' => $wiki->subscribers()->forUser($this->userId)->exists(),
            'flag' => $this->getGateFactory()->allows('wiki-admin') ? $this->getFlagFactory()->takeForWiki($wiki->id) : '',
            'form' => $this->createForm(CommentForm::class, [], [
                'url' => route('wiki.comment.save', [$wiki->id])
            ]),
            'children' => $this->getCatalog($wiki->id)
        ]);

        if (method_exists($this, $wiki->template)) {
            $view->with($this->{$wiki->template}($wiki));
        }

        return $view;
    }

    /**
     * @param Wiki $wiki
     * @return array
     */
    public function show(Wiki $wiki)
    {
        return [
            'mlt' => $this->getMoreLikeThis($wiki),
            'related' => $this->getRelated($wiki->id)
        ];
    }

    /**
     * @param Wiki $wiki
     * @return array
     */
    public function category(Wiki $wiki)
    {
        return [
            'folders' => $this->getFolders($wiki->id)
        ];
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

    /**
     * @param Wiki $wiki
     * @return mixed
     */
    private function getMoreLikeThis(Wiki $wiki)
    {
        return $this->getCacheFactory()->remember('wiki:mlt', 60 * 24, function () use ($wiki) {
            return $this->wiki->search(new MoreLikeThisBuilder($wiki))->getSource();
        });
    }

    /**
     * @param $wikiId
     * @return \Coyote\Wiki[]
     */
    private function getRelated($wikiId)
    {
        return $this->wiki->getRelatedPages($wikiId);
    }
}
