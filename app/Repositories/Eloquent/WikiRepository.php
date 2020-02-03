<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\SubscribableInterface;
use Coyote\Repositories\Contracts\WikiRepositoryInterface;
use Coyote\Wiki;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

/**
 * @method $this withTrashed()
 */
class WikiRepository extends Repository implements WikiRepositoryInterface, SubscribableInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Wiki';
    }

    /**
     * @param string $path
     * @return WikiRepository
     */
    public function findByPath($path)
    {
        return $this->applyCriteria(function () use ($path) {
            // we need to get page by path. there can be more than one page of giving location.
            // one can be deleted but we have to retrieve the newest one.
            return $this
                ->model
                ->whereRaw('LOWER(path) = ?', [mb_strtolower($path)])
                ->orderBy('wiki_id', 'DESC') // <-- DO NOT remove this line
                ->first();
        });
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function findNewLocation($path)
    {
        return $this
            ->app
            ->make(Wiki\Redirect::class)
            ->select(['wiki_paths.path'])
            ->join('wiki_paths', 'wiki_paths.path_id', '=', $this->raw('wiki_redirects.path_id'))
            ->whereRaw('LOWER(wiki_redirects.path) = ?', [mb_strtolower($path)])
            ->whereNull('wiki_paths.deleted_at')
            ->orderBy('wiki_redirects.id', 'DESC')
            ->first();
    }

    /**
     * Get children articles of given parent_id.
     *
     * @param int|null $parentId
     * @return mixed
     */
    public function children($parentId = null)
    {
        return $this->prepareChildren($parentId)->get();
    }

    /**
     * @param int|null $parentId
     * @return mixed
     */
    public function getCatalog($parentId = null)
    {
        $comments = $this
            ->app
            ->make(Wiki\Comment::class)
            ->select([$this->raw('COUNT(*)')])
            ->where('wiki_id', '=', $this->raw('wiki.id'))
            ->toSql();

        return $this
            ->prepareChildren($parentId)
            ->select([
                'wiki.*',
                'users.name AS user_name',
                'users.photo',
                'users.id AS user_id',
                $this->raw("($comments) AS comments")
            ])
            ->join('users', function (JoinClause $join) {
                $sub = $this
                        ->app
                        ->make(Wiki\Log::class)
                        ->select(['user_id'])
                        ->where('wiki_id', '=', $this->raw('wiki.wiki_id'))
                        ->orderBy('id')
                        ->limit(1)
                        ->toSql();

                $join->on('users.id', '=', $this->raw("($sub)"));
            })
            ->where('parent_id', $parentId)
            ->where('children', 0)
            ->orderBy('created_at', 'DESC')
            ->paginate();
    }

    /**
     * @param int $pathId
     * @return mixed
     */
    public function parents($pathId)
    {
        $this->applyCriteria();
        return $this->model->from($this->rawFunction('wiki_parents', $pathId))->get();
    }

    /**
     * @return array
     */
    public function treeList()
    {
        $this->applyCriteria();

        $result = [];
        $data = $this->model->from($this->rawFunction('wiki_children'))->get(['id', 'title', 'depth']);

        foreach ($data as $row) {
            $result[$row['id']] = str_repeat('&nbsp;', $row['depth'] * 4) . $row['title'];
        }

        return $result;
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function getSubscribed($userId)
    {
        return $this
            ->app
            ->make(Wiki\Subscriber::class)
            ->select()
            ->join('wiki', 'wiki.wiki_id', '=', 'wiki_subscribers.wiki_id')
            ->where('wiki_subscribers.user_id', $userId)
            ->whereNull('deleted_at')
            ->orderBy('wiki_subscribers.id', 'DESC')
            ->paginate();
    }

    /**
     * @param \Coyote\Wiki $wiki
     * @param Request $request
     */
    public function save($wiki, Request $request)
    {
        /** @var \Coyote\Wiki\Page $page */
        $page = $this->app->make(Wiki\Page::class)->findOrNew($wiki->wiki_id);

        $page->fill($request->all());
        $page->fillGuarded($request->only(['is_locked', 'template']), $request->user()->can('wiki-admin'));

        // we need to know if those attributes were changed. if so, we need to add new record to the history.
        $isDirty = $page->isDirty(['title', 'excerpt', 'text']);
        $page->save();

        if ($isDirty) {
            $length = mb_strlen($page->text);
            $diff = $length;

            if ($wiki->exists) {
                $old = $page->logs()->orderBy('id', 'DESC')->value('text');
                // @todo make real diff
                $diff = $length - mb_strlen($old);
            }

            // add new version to the history
            $page->logs()->create($page->toArray() + [
                'user_id'   => $request->user()->id,
                'ip'        => $request->ip(),
                'host'      => $request->getClientHost(),
                'browser'   => $request->browser(),
                'length'    => $length,
                'diff'      => $diff,
                'comment'   => $request->input('comment')
            ]);

            $this->calculateAuthorsShare($page->id);
        }

        $page->syncAttachments(array_pluck($request->get('attachments', []), 'id'));

        if ($page->wasRecentlyCreated) {
            $parent = $this->app->make(Wiki\Path::class)->findOrNew((int) $request->input('parent_id'));
            $wiki->forceFill($page->createPath($parent, $page->slug)->toArray());

            $wiki->id = $wiki->path_id;
        }

        $wiki->forceFill(array_except($page->toArray(), ['id']));
        $wiki->wasRecentlyCreated = $page->wasRecentlyCreated;
    }

    /**
     * @param int $pathId
     * @return \Coyote\Wiki[]
     */
    public function getRelatedPages($pathId)
    {
        return $this
            ->model
            ->selectRaw('DISTINCT ON(wiki_id) wiki.path, title, long_title, updated_at')
            ->join('wiki_links', 'wiki_links.path_id', '=', 'wiki.id')
            ->where('wiki_links.ref_id', $pathId)
            ->limit(10)
            ->get();
    }

    /**
     * @param string $path
     * @return \Coyote\Wiki[]
     */
    public function getWikiAssociatedLinksByPath($path)
    {
        return $this
            ->model
            ->join('wiki_links', 'wiki_links.path_id', '=', 'wiki.id')
            ->whereRaw('LOWER(wiki_links.path) = ?', [mb_strtolower($path)])
            ->get(['wiki.id', 'wiki_id', 'text']);
    }

    /**
     * New page was created so we need to fix broken links.
     *
     * @param string $path
     * @param int $pathId
     */
    public function associateLink($path, $pathId)
    {
        $this->updateLinks($path, $pathId);
    }

    /**
     * $path was deleted so we need to dissociate links.
     *
     * @param string $path
     */
    public function dissociateLink($path)
    {
        $this->updateLinks($path, null);
    }

    /**
     * Update wiki_links table by path.
     *
     * @param string $path
     * @param int $pathId
     */
    private function updateLinks($path, $pathId)
    {
        $this->app->make(Wiki\Link::class)->whereRaw('LOWER(path) = ?', [mb_strtolower($path)])->update(['ref_id' => $pathId]);
    }

    /**
     * @param int $wikiId
     */
    private function calculateAuthorsShare($wikiId)
    {
        $totalDiff = $this
            ->app
            ->make(Wiki\Log::class)
            ->where('wiki_id', $wikiId)
            ->where('diff', '>', 0)
            ->where('is_restored', 0)
            ->orderBy('id')
            ->get(['diff'])
            ->sum('diff');

        $this->app->make(Wiki\Author::class)->where('wiki_id', $wikiId)->delete();

        $insert = "INSERT INTO wiki_authors (wiki_id, user_id, share, length)
                   SELECT wiki_id, user_id, SUM(diff::FLOAT) / $totalDiff * 100, SUM(diff) 
                   FROM (SELECT * FROM wiki_log WHERE wiki_id = $wikiId AND diff > 0 AND is_restored = 0 ORDER BY id) AS t 
                   GROUP BY user_id, wiki_id";

        $this->app->make('db')->insert($insert);
    }

    /**
     * @param int $wikiId
     * @param int $pathId
     * @return Wiki\Page
     */
    public function clone($wikiId, $pathId)
    {
        $parent = $this->getPath($pathId);
        $page = $this->getPage($wikiId);

        return $page->createPath($parent, $page->slug);
    }

    /**
     * @param int $id   Current path id
     * @param int $wikiId   Current page id
     * @param int|null $pathId   New path id
     * @return \Coyote\Wiki\Path
     */
    public function move($id, $wikiId, $pathId)
    {
        // current path
        $current = $this->getPath($id);

        // new path
        $path = $this->getPath($pathId);
        // current page
        $page = $this->getPage($wikiId);

        // make a redirection so old links will redirect to new path
        $this->app->make(Wiki\Redirect::class)->create(['path' => $current->path, 'path_id' => $current->path_id]);

        // update current path and parent id
        $current->update(['path' => $page->makePath($path->path, $page->slug), 'parent_id' => $pathId]);
        return $current;
    }

    /**
     * @param int $wikiId
     * @return mixed
     */
    public function delete($wikiId)
    {
        return $this->app->make(Wiki\Page::class)->destroy($wikiId);
    }

    /**
     * @param int $pathId
     * @return mixed
     */
    public function unlink($pathId)
    {
        return $this->app->make(Wiki\Path::class)->destroy($pathId);
    }

    /**
     * @param int $pathId
     * @return mixed
     */
    public function restore($pathId)
    {
        return $this->app->make(Wiki\Path::class)->withTrashed()->findOrFail($pathId)->restore();
    }

    /**
     * @param int $wikiId
     * @return Wiki[]
     */
    public function getAllCategories($wikiId)
    {
        return $this
            ->model
            ->select(['parent.*'])
            ->where('wiki.wiki_id', $wikiId)
            ->join('wiki AS parent', 'parent.id', '=', 'wiki.parent_id')
            ->get();
    }

    /**
     * @inheritdoc
     */
    public function getLogBuilder()
    {
        $this->applyCriteria();

        return $this
            ->app
            ->make(Wiki\Log::class)
            ->select([
                'wiki_log.id',
                'wiki_log.title',
                'wiki_log.created_at',
                'wiki_log.comment',
                'wiki_log.length',
                'wiki_log.diff',
                'wiki_log.user_id',
                'users.name AS user_name',
                'path'
            ])
            ->join('users', 'users.id', '=', 'user_id')
            ->join('wiki_pages', 'wiki_pages.id', '=', 'wiki_log.wiki_id')
            ->join('wiki_paths', 'wiki_paths.wiki_id', '=', 'wiki_log.wiki_id')
            ->whereNull('wiki_pages.deleted_at');
    }

    /**
     * @param int $pathId
     * @return \Coyote\Wiki\Path
     */
    private function getPath($pathId)
    {
        // findOrNew() because $pathId can be null.
        return $this->app->make(Wiki\Path::class)->findOrNew((int) $pathId);
    }

    /**
     * @param int $wikiId
     * @return \Coyote\Wiki\Page
     */
    private function getPage($wikiId)
    {
        return $this->app->make(Wiki\Page::class)->find($wikiId);
    }

    /**
     * @param string $name
     * @param array ...$args
     * @param integer $args
     * @return \Illuminate\Database\Query\Expression
     */
    private function rawFunction($name, ...$args)
    {
        foreach ($args as &$arg) {
            if ($arg === null) {
                $arg = 'NULL';
            }
        }

        return $this->raw(sprintf('%s(%s) AS "wiki"', $name, implode(',', $args)));
    }

    /**
     * @param int $parentId
     * @return mixed
     */
    private function prepareChildren($parentId)
    {
        $this->applyCriteria();

        return $this->model->from($this->rawFunction('wiki_children', $parentId));
    }
}
