<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Http\Resources\Api\MicroblogResource;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\Microblog\OrderById;
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
     * @param int $id
     * @return MicroblogResource
     */
    public function show(int $id)
    {
        MicroblogResource::withoutWrapping();

        $microblog = $this->microblog->findById($id);
        return new MicroblogResource($microblog);
    }

    /**
     * @return array
     */
    private function relations()
    {
        return [
            'comments.user'
        ];
    }
}
