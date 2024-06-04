<?php
namespace Coyote\Domain\Administrator\UserMaterial\Store;

use Coyote\Domain\Administrator\UserMaterial\Material;
use Coyote\Microblog;
use Coyote\Post;
use Illuminate\Database\Eloquent;

class MaterialStore
{
    public function fetch(MaterialRequest $request): MaterialResult
    {
        $materials = $this
            ->queryByType($request->type)
            ->offset(($request->page - 1) * $request->pageSize)
            ->limit($request->pageSize)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->map(fn(Post|Microblog|Post\Comment $material) => new Material(
                $request->type,
                $material->created_at,
                $material->text))
            ->toArray();

        return new MaterialResult(
            $materials,
            $this->queryByType($request->type)->count(),
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
}
