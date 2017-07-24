<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Job\Preferences;
use Illuminate\Http\Request;

class PreferencesController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function index(Request $request)
    {
        $validator = $this->getValidationFactory()->make($request->all(), [
            'tags.*'        => 'max:25|tag',
            'city'          => 'string|city',
            'salary'        => 'int',
            'currency_id'   => 'required|integer',
            'is_remote'     => 'bool'
        ]);

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

        $this->setSetting('job.preferences', Preferences::make($request->except('_token')));

        return response(route('job.home'));
    }

    /**
     * @param Request $request
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    protected function throwValidationException(Request $request, $validator)
    {
        $messages = $validator->errors();

        foreach ($messages->messages() as $field => $errors) {
            if (substr($field, 0, 4) === 'tags') {
                $validator->errors()->add('tags', $errors[0]);
            }
        }

        parent::throwValidationException($request, $validator);
    }
}
