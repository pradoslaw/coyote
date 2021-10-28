<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Http\Resources\GuideResource;
use Coyote\Guide;
use Coyote\Http\Resources\TagResource;

class ShowController extends BaseController
{
    public function index(Guide $guide)
    {
        $this->breadcrumb->push('Pytania kwalifikacyjne');

        TagResource::urlResolver(fn (string $name) => route('guide.tag', [urlencode($name)]));

        $guide->loadCount('comments');
        $guide->load(['commentsWithChildren', 'subscribers']);
        $guide->loadUserVoterRelation($this->userId);

        return $this->view('guide.show', [
            'guide'         => new GuideResource($guide)
        ]);
    }
}
