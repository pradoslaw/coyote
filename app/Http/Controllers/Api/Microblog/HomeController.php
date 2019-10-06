<?php

namespace Coyote\Http\Controllers\Api\Microblog;

use Coyote\Http\Resources\MicroblogResource;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\Microblog\OrderById;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    /**
     * @param MicroblogRepository $microblog
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(MicroblogRepository $microblog)
    {
        $microblog->pushCriteria(new EagerLoading([
            'comments' => function (HasMany $query) {
                return $query->with([
                    'user' => function (BelongsTo $query) {
                        return $query->select(['id', 'name', 'photo'])->withTrashed();
                    }
                ]);
            },
            'user' => function (BelongsTo $query) {
                return $query->select(['id', 'name', 'photo'])->withTrashed();
            }
        ]));
        $microblog->pushCriteria(new OrderById(false));

        $data = $microblog->paginate();

        return MicroblogResource::collection($data);
    }
}
