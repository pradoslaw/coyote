<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\FlagFactory;
use Coyote\Http\Resources\FlagResource;
use Coyote\Microblog;

class BaseController extends Controller
{
    use FlagFactory;

    /**
     * @param Microblog|\Illuminate\Pagination\LengthAwarePaginator $collection
     * @return array
     */
    protected function flags($collection): array
    {
        if (!$this->auth->can('microblog-delete')) {
            return [];
        }

        if ($collection instanceof Microblog) {
            $ids = array_merge([$collection->id], $collection->comments->pluck('id')->toArray());
        } else {
            $ids = $collection->pluck('id')->toArray();

            foreach ($collection as $microblog) {
                $ids = array_merge($ids, $microblog->comments->pluck('id')->toArray());
            }
        }

        $repository = $this->getFlagFactory();
        $flags = $repository->findAllByModel(Microblog::class, $ids);

        return FlagResource::collection($flags)->toArray($this->request);
    }
}
