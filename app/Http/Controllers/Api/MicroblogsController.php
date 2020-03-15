<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Http\Resources\Api\MicroblogResource;
use Coyote\Microblog;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\Microblog\OrderById;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Routing\Controller;

class MicroblogsController extends Controller
{
    /**
     * @var MicroblogRepository
     */
    private $microblog;

    /**
     * @param MicroblogRepository $microblog
     */
    public function __construct(MicroblogRepository $microblog)
    {
        $this->microblog = $microblog;
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $this->microblog->pushCriteria(new EagerLoading($this->relations()));
        $this->microblog->pushCriteria(new OrderById(false));

        $data = $this->microblog->paginate();

        return MicroblogResource::collection($data);
    }

    /**
     * @param Microblog $microblog
     * @return MicroblogResource
     */
    public function show(Microblog $microblog)
    {
        Resource::withoutWrapping();

        $microblog->load($this->relations());

        return new MicroblogResource($microblog);
    }

    /**
     * @return array
     */
    private function relations()
    {
        return [
            'comments' => function (HasMany $query) {
                return $query->with([
                    'user' => function (BelongsTo $query) {
                        return $query->select(['id', 'name', 'photo', 'is_blocked', 'deleted_at'])->withTrashed();
                    }
                ]);
            },
            'user' => function (BelongsTo $query) {
                return $query->select(['id', 'name', 'photo', 'is_blocked', 'deleted_at'])->withTrashed();
            }
        ];
    }
}
