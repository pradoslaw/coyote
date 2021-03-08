<?php

namespace Coyote\Http\Controllers\Questions;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Resources\QuestionResource;
use Coyote\Models\Question;

class ShowController extends Controller
{
    public function index(Question $question)
    {
        $this->breadcrumb->push('Pytania kwalifikacyjne');

        return $this->view('questions.show', ['question' => new QuestionResource($question)]);
    }
}
