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
        $form = $this->getForm($wiki);
        $this->breadcrumb->push('Edycja strony');

        return $this->view('wiki.submit')->with(compact('form', 'wiki'));
    }

    /**
     * @param \Coyote\Wiki $wiki
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($wiki)
    {
        $form = $this->getForm($wiki);
        $form->validate();

        $request = $form->getRequest();

        $path = $this->transaction(function () use ($wiki, $request) {
            $subscribe = auth()->user()->allow_subscribe && !$wiki->wasUserInvolved($this->userId);
            $this->wiki->save($wiki, $request);

            $subscribersId = $wiki->subscribers()->lists('user_id')->toArray();

            app('alert.wiki.subscriber')
                ->with([
                    'subject' => $wiki->title,
                    'users_id' => $subscribersId,
                    'url' => route('wiki.show', [$wiki->path], false),
                    'sender_id' => $this->userId,
                    'sender_name' => auth()->user()->name,
                    'excerpt' => excerpt($wiki->text)
                ])
                ->notify();

            // we DO NOT want to add another row into the table. we MUST check whether user is already
            // on subscribers list or not.
            if ($subscribe && !in_array($this->userId, $subscribersId)) {
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

    /**
     * @param \Coyote\Wiki $wiki
     * @return \Coyote\Services\FormBuilder\Form
     */
    protected function getForm($wiki)
    {
        return $this->createForm(WikiForm::class, $wiki, [
            'url' => route('wiki.submit', [$wiki->id])
        ]);
    }
}
