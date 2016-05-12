<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Forms\Wiki\WikiForm;
use Illuminate\Http\Request;

class SubmitController extends BaseController
{
    /**
     * @param \Coyote\Wiki $wiki
     * @param Request $request
     * @return $this
     */
    public function index($wiki, Request $request)
    {
        $form = $this->createForm(WikiForm::class, $wiki, [
            'url' => route('wiki.submit', [$wiki->id])
        ]);

        if ($request->has('parentId')) {
            $form->get('parent_id')->setValue($request->get('parentId'));
        }

        $this->breadcrumb->push('Edycja strony');

        return $this->view('wiki.submit')->with(compact('form', 'wiki'));
    }

    /**
     * @param \Coyote\Wiki $wiki
     * @param WikiForm $form
     */
    public function save($wiki, WikiForm $form)
    {
        $request = $form->getRequest();
        $wiki->fill($request->all());

        $path = \DB::transaction(function () use ($wiki, $request) {
            $wiki->save();
            // add new version to the history
            $wiki->logs()->create($wiki->toArray() + [
                'user_id'   => $this->userId,
                'ip'        => $request->ip(),
                'host'      => $request->getHost(),
                'browser'   => $request->browser()
            ]);

            return $wiki->path;
        });

        return redirect()->to($path)->with('success', 'Zmiany zostały zapisane.');
    }
}
