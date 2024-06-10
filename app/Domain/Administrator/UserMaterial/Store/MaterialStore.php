<?php
namespace Coyote\Domain\Administrator\UserMaterial\Store;

use Carbon\Carbon;
use Coyote\Domain\Administrator\UserMaterial\Material;
use Coyote\Microblog;
use Coyote\Models\Flag\Resource;
use Coyote\Post;
use Illuminate\Database\Eloquent;

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

        $builder = $query->clone();
        $table = $query->getModel()->getTable();
        $materials = $query
            ->select("$table.*", 'users.name AS username', 'users.photo AS user_photo')
            ->offset(($request->page - 1) * $request->pageSize)
            ->leftJoin('users', 'users.id', '=', 'user_id')
            ->limit($request->pageSize)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->map(fn(Post|Microblog|Post\Comment $material) => new Material(
                $request->type,
                $material->created_at,
                $this->deletedAt($material),
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

    private function deletedAt(Post|Post\Comment|Microblog $material): ?Carbon
    {
        if (\is_string($material->deleted_at)) {
            return new Carbon($material->deleted_at);
        }
        return $material->deleted_at;
    }
}
