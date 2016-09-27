<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Events\WikiWasSaved;
use Coyote\Http\Forms\Wiki\WikiForm;
use Coyote\Services\Stream\Objects\Wiki as Stream_Wiki;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Http\Request;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;

class SubmitController extends BaseController
{
    /**
     * @param Request $request
     * @param WikiRepository $wiki
     */
    public function __construct(Request $request, WikiRepository $wiki)
    {
        parent::__construct($request, $wiki);

        $this->breadcrumb->push('Edycja strony');
    }

    /**
     * @param \Coyote\Wiki $wiki
     * @return \Illuminate\View\View
     */
    public function index($wiki, Request $request)
    {
        $form = $this->getForm($wiki);

        if (!$wiki->exists) {
            $form->get('parent_id')->setValue($request->input('parentId'));
        }

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
                    'url' => UrlBuilder::wiki($wiki),
                    'sender_id' => $this->userId,
                    'sender_name' => auth()->user()->name,
                    'excerpt' => excerpt($wiki->excerpt)
                ])
                ->notify();

            // we DO NOT want to add another row into the table. we MUST check whether user is already
            // on subscribers list or not.
            if ($subscribe && !in_array($this->userId, $subscribersId)) {
                $wiki->subscribers()->create(['user_id' => $this->userId]);
            }

            app('reputation.wiki.' . ($wiki->wasRecentlyCreated ? 'create' : 'update'))->map($wiki)->save();

            stream(
                $wiki->wasRecentlyCreated ? Stream_Create::class : Stream_Update::class,
                (new Stream_Wiki())->map($wiki)
            );
            // add to elasticsearch index and pages table...
            event(new WikiWasSaved($wiki));

            return $wiki->path;
        });

        return redirect()->to($path)->with('success', 'Zmiany zostały zapisane.');
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        return response($this->getParser()->setEnableCache(false)->parse((string) $request->input('text')));
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
