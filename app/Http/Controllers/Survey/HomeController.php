<?php
namespace Coyote\Http\Controllers\Survey;

use Coyote\Domain\Survey\GuestSurvey;
use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    public function store(GuestSurvey $survey): Response|null
    {
        if ($this->request->has('surveyState')) {
            $survey->setState($this->request->get('surveyState'));
            return null;
        }
        if ($this->request->has('surveyChoice')) {
            $survey->setChoice($this->request->get('surveyChoice'));
            return null;
        }
        if ($this->request->has('surveyChoicePreview')) {
            $survey->preview($this->request->get('surveyChoicePreview'));
            return null;
        }
        return response(status:422);
    }
}
