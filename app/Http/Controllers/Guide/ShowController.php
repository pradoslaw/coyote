<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Http\Resources\GuideResource;
use Coyote\Guide;
use Coyote\Services\UrlBuilder;

class ShowController extends BaseController
{
    public function index(Guide $guide)
    {
        $this->breadcrumb->push($guide->title, UrlBuilder::guide($guide));

        $guide->loadCount('comments');
        $guide->load(['commentsWithChildren', 'subscribers']);
        $guide->loadUserVoterRelation($this->userId);
        $guide->loadUserRoleRelation($this->userId);

        GuideResource::withoutWrapping();

        return $this->view('guide.show', [
            'guide'         => (new GuideResource($guide))->toResponse($this->request)->getData(true)
        ]);
    }
}
