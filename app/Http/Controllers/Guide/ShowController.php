<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Resources\GuideResource;
use Coyote\Guide;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;

class ShowController extends Controller
{
    public function __construct(protected TagRepository $tagRepository)
    {
        parent::__construct();
    }

    public function index(Guide $guide)
    {
        $this->breadcrumb->push('Pytania kwalifikacyjne');

        $guide->loadCount('comments');
        $guide->load(['commentsWithChildren', 'subscribers']);
        $guide->loadUserVoterRelation($this->userId);

        return $this->view('guide.show', [
            'guide'         => new GuideResource($guide),
            'popular_tags'  => $this->tagRepository->popularTags(Guide::class)->groupBy('category')
        ]);
    }
}
