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

        $path = $this->transaction(function () use ($wiki, $request) {
            $subscribe = auth()->user()->allow_subscribe
                && ($wiki->wasRecentlyCreated || !$wiki->wasUserInvolved($this->userId));
            $this->wiki->save($wiki, $request);

            if ($subscribe) {
                $wiki->subscribers()->create(['user_id' => $this->userId]);
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
