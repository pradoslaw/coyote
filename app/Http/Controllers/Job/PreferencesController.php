<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PreferencesController extends Controller
{
    /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        $validator = $this->getValidationFactory()->make($request->all(), [
            'tags.*' => 'max:25|tag',
            'city' => 'string|city',
            'salary' => 'int',
            'currency_id' => 'required|integer',
            'is_remote' => 'bool'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors();

            foreach ($messages->messages() as $field => $errors) {
                if (substr($field, 0, 4) === 'tags') {
                    $validator->errors()->add('tags', $errors[0]);
                }
            }

            $this->throwValidationException($request, $validator);
        }

        $this->setSetting('job.preferences', json_encode($request->except('_token')));
        return response(route('job.home', ['tab' => HomeController::TAB_FILTERED]));
    }
}
