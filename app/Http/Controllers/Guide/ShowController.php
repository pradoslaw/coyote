<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Resources\GuideResource;
use Coyote\Models\Guide;

class ShowController extends Controller
{
    public function index(Guide $question)
    {
        $this->breadcrumb->push('Pytania kwalifikacyjne');

        return $this->view('questions.show', ['question' => new GuideResource($question)]);
    }
}
