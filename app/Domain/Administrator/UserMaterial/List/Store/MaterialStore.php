<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\Store;

use Carbon\Carbon;
use Coyote\Domain\Administrator\UserMaterial\Material;
use Coyote\Microblog;
use Coyote\Models\Flag\Resource;
use Coyote\Post;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Query;

class MaterialStore
{
    public function fetch(MaterialRequest $request): MaterialResult
    {
        /** @var Eloquent\Builder $query */
        $query = $this->queryByType($request->type)->withTrashed();

        if ($request->deleted === true) {
            $query->onlyTrashed();
        }
        if ($request->deleted === false) {
            $query->withoutTrashed();
        }

        $materialTable = $query->getModel()->getTable();
        $flagResourceTable = (new Resource)->getTable();

        if ($request->reported !== null) {
            $query->whereExists(
                callback:fn(Query\Builder $query) => $query
                    ->from($flagResourceTable)
                    ->whereColumn("$flagResourceTable.resource_id", "$materialTable.id")
                    ->where("$flagResourceTable.resource_type", $this->resourceClassByType($request->type)),
                not:!$request->reported);
        }
        if ($request->authorId !== null) {
            $query->where('user_id', $request->authorId);
        }
        $builder = $query->clone();
        $materials = $query
            ->select("$materialTable.*", 'users.name AS username', 'users.photo AS user_photo')
            ->offset(($request->page - 1) * $request->pageSize)
            ->leftJoin('users', 'users.id', '=', 'user_id')
            ->limit($request->pageSize)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->map(fn(Post|Microblog|Post\Comment $material) => new Material(
                $material->id,
                $request->type,
                $material->created_at,
                $this->deletedAt($material),
                $this->parentDeletedAt($material),
                $material->username ?? '',
                $material->user_photo,
                $material->text,
                Resource::query()
                    ->where('resource_type', \get_class($material))
                    ->where('resource_id', $material->id)
                    ->exists(),
            ))
            ->toArray();

        return new MaterialResult(
            $materials,
            $builder->count(),
        );
    }

    private function queryByType(string $type): Eloquent\Builder
    {
        if ($type === 'comment') {
            return Post\Comment::query();
        }
        if ($type === 'post') {
            return Post::query();
        }
        return Microblog::query();
    }

    private function resourceClassByType(string $type): string
    {
        if ($type === 'comment') {
            return Post\Comment::class;
        }
        if ($type === 'post') {
            return Post::class;
        }
        return Microblog::class;
    }

    private function deletedAt(Post|Post\Comment|Microblog $material): ?Carbon
    {
        if (\is_string($material->deleted_at)) {
            return new Carbon($material->deleted_at);
        }
        return $material->deleted_at;
    }

    private function parentDeletedAt(Post|Post\Comment|Microblog $material): ?Carbon
    {
        if ($material instanceof Post\Comment) {
            return $this->deletedAt($material->post);
        }
        return null;
    }
}
