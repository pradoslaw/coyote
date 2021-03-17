<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Resources\GuideResource;
use Coyote\Models\Guide;

class ShowController extends Controller
{
    public function index(Guide $guide)
    {
        $this->breadcrumb->push('Pytania kwalifikacyjne');

        return $this->view('guide.show', ['guide' => new GuideResource($guide)]);
    }
}
