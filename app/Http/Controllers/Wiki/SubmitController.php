<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Events\WikiSaved;
use Coyote\Http\Forms\Wiki\SubmitForm;
use Coyote\Notifications\Wiki\ContentChangedNotification;
use Coyote\Services\Stream\Objects\Wiki as Stream_Wiki;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Http\Request;

class SubmitController extends BaseController
{
    /**
     * @param \Coyote\Wiki $wiki
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index($wiki, Request $request)
    {
        $form = $this->getForm($wiki);
        $this->request->attributes->set('upload_url', route('wiki.upload'));

        if (!$wiki->exists) {
            $form->get('parent_id')->setValue($request->input('parentId'));
        }

        $this->breadcrumb->push($wiki->title, url($wiki->path));
        $this->breadcrumb->push($wiki->exists ? 'Edycja strony' : 'Dodaj nową stronę');

        return $this->view('wiki.submit')->with(compact('form', 'wiki'));
    }

    /**
     * @param string $path
     * @return \Illuminate\View\View
     */
    public function create($path)
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

    /**
     * @param Dispatcher $dispatcher
     * @param \Coyote\Wiki $wiki
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Dispatcher $dispatcher, $wiki)
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

        return redirect()->to($wiki->path)->with('success', 'Zmiany zostały zapisane.');
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        $parser = $this->getParser();
        $parser->cache->setEnable(false);

        return response($parser->parse((string) $request->input('text')));
    }

    /**
     * @param \Coyote\Wiki $wiki
     * @return \Coyote\Services\FormBuilder\Form
     */
    protected function getForm($wiki)
    {
        return $this->createForm(SubmitForm::class, $wiki, [
            'url' => route('wiki.submit', [$wiki->id])
        ]);
    }
}
