<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Http\Resources\GuideResource;
use Coyote\Repositories\Contracts\GuideRepositoryInterface as GuideRepository;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Repositories\Criteria\WithTags;

class HomeController extends BaseController
{
    public function __construct(
        protected TagRepository $tagRepository,
        protected GuideRepository $guideRepository)
    {
        parent::__construct($this->tagRepository);
    }

    public function index()
    {
        return $this->load();
    }

    public function filterByTags(string $tag)
    {
        $this->guideRepository->pushCriteria(new WithTags([$tag]));

        return $this->load();
    }

    private function load()
    {
        $paginator = $this->guideRepository->paginate();

        return $this->view('guide.home', [
            'pagination'                => GuideResource::collection($paginator)->response()->getData(true)
        ]);
    }
}
