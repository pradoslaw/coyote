<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Events\WikiWasSaved;
use Coyote\Http\Forms\Wiki\WikiForm;
use Coyote\Services\Stream\Objects\Wiki as Stream_Wiki;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;

class SubmitController extends BaseController
{
    /**
     * @param \Coyote\Wiki $wiki
     * @return $this
     */
    public function index($wiki)
    {
        $form = $this->createForm(WikiForm::class, $wiki, [
            'url' => route('wiki.submit', [$wiki->id])
        ]);

        $this->breadcrumb->push('Edycja strony');

        return $this->view('wiki.submit')->with(compact('form', 'wiki'));
    }

    /**
     * @param \Coyote\Wiki $wiki
     * @param WikiForm $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($wiki, WikiForm $form)
    {
        $request = $form->getRequest();

        $wiki->fill($request->all());
        $wiki->fillGuarded($request->only(['is_locked', 'template']), $request->user()->can('wiki-admin'));

        $path = $this->transaction(function () use ($wiki, $request) {
            // we need to know if those attributes were changed. if so, we need to add new record to the history.
            $isDirty = $wiki->isDirty(['title', 'parent_id', 'excerpt', 'text']);
            $wiki->save();

            if ($isDirty) {
                // add new version to the history
                $wiki->logs()->create($wiki->toArray() + [
                    'user_id'   => $this->userId,
                    'ip'        => $request->ip(),
                    'host'      => gethostbyaddr($request->ip()),
                    'browser'   => $request->browser(),
                    'length'    => mb_strlen($wiki->text)
                ]);
            }

            stream(
                $wiki->wasRecentlyCreated ? Stream_Create::class : Stream_Update::class,
                (new Stream_Wiki())->map($wiki)
            );
            // add to elasticsaech index and pages table...
            event(new WikiWasSaved($wiki));

            return $wiki->path;
        });

        return redirect()->to($path)->with('success', 'Zmiany zostały zapisane.');
    }
}
