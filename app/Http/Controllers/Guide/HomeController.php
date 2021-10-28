<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Http\Resources\GuideResource;
use Coyote\Repositories\Contracts\GuideRepositoryInterface as GuideRepository;

class HomeController extends BaseController
{
    public function index(GuideRepository $repository)
    {


        $paginator = $repository->paginate();

        return $this->view('guide.home', [
            'pagination'                => GuideResource::collection($paginator)->response()->getData(true)
        ]);
    }
}
