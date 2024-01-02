<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Events\WikiSaved;
use Coyote\Http\Forms\Wiki\SubmitForm;
use Coyote\Notifications\Wiki\ContentChangedNotification;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Wiki as Stream_Wiki;
use Coyote\Wiki;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubmitController extends BaseController
{
    public function index(Wiki $wiki, Request $request): View
    {
        $form = $this->getForm($wiki);
        $this->request->attributes->set('upload_url', route('wiki.upload'));

        if ($wiki->exists) {
            $this->breadcrumb->push($wiki->title, url($wiki->path));
            $this->breadcrumb->push(
                'Edycja strony',
                route('wiki.edit', ['wiki' => $wiki->id]));
        } else {
            $parentId = $request->input('parentId');
            $form->get('parent_id')->setValue($parentId);
            $this->breadcrumb->push(
                'Dodaj nową stronę',
                route('wiki.submit', ['parentId' => $parentId]));
        }

        return $this->view('wiki.submit')->with(compact('form', 'wiki'));
    }

    public function create(string $path): View
    {
        $form = $this->getForm($this->wiki->newInstance());
        $segments = explode('/', trim($path, '/'));

        $form->get('title')->setValue(str_replace('_', ' ', array_pop($segments)));
        $parent = $this->wiki->findByPath(implode('/', $segments));

        if ($parent) {
            $form->get('parent_id')->setValue($parent->id);
        }

        return $this->view('wiki.submit')->with(compact('form'));
    }

    public function save(Dispatcher $dispatcher, Wiki $wiki): RedirectResponse
    {
        $form = $this->getForm($wiki);
        $form->validate();

        $request = $form->getRequest();
        $subscribers = $wiki->subscribers()->with('user')->get()->pluck('user');

        $this->transaction(function () use ($wiki, $request, $subscribers) {
            $subscribe = auth()->user()->allow_subscribe && !$wiki->wasUserInvolved($this->userId);
            $this->wiki->save($wiki, $request);

            // we DO NOT want to add another row into the table. we MUST check whether user is already
            // on subscribers list or not.
            if ($subscribe && !$subscribers->contains('id', $this->userId)) {
                $wiki->subscribers()->create(['user_id' => $this->userId]);
            }

            app('reputation.wiki.' . ($wiki->wasRecentlyCreated ? 'create' : 'update'))->map($wiki)->save();

            stream(
                $wiki->wasRecentlyCreated ? Stream_Create::class : Stream_Update::class,
                (new Stream_Wiki())->map($wiki)
            );
        });

        $dispatcher->send(
            $subscribers->exceptUser($this->auth),
            new ContentChangedNotification($wiki)
        );

        // add to elasticsearch index and pages table...
        event(new WikiSaved($wiki));

        return redirect()
            ->to($wiki->path)
            ->with('success', 'Zmiany zostały zapisane.');
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        $parser = $this->getParser();
        $parser->cache->setEnable(false);

        return response($parser->parse((string)$request->input('text')));
    }

    private function getForm(Wiki $wiki): Form
    {
        return $this->createForm(SubmitForm::class, $wiki, [
            'url' => route('wiki.submit', [$wiki->id]),
        ]);
    }
}
