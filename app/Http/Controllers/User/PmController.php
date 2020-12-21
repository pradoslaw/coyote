<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Events\PmCreated;
use Coyote\Events\PmRead;
use Coyote\Http\Requests\PmRequest;
use Coyote\Http\Resources\PmResource;
use Coyote\Notifications\PmCreatedNotification;
use Coyote\Pm;
use Coyote\Repositories\Contracts\NotificationRepositoryInterface as NotificationRepository;
use Coyote\Repositories\Contracts\PmRepositoryInterface as PmRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\User;
use Illuminate\Http\Request;

/**
 * Class PmController
 * @package Coyote\Http\Controllers\User
 */
class PmController extends BaseController
{
    use HomeTrait;

    /**
     * @var UserRepository
     */
    private $user;

    /**
     * @var NotificationRepository
     */
    private $notification;

    /**
     * @var PmRepository
     */
    private $pm;

    /**
     * @param UserRepository $user
     * @param NotificationRepository $notification
     * @param PmRepository $pm
     */
    public function __construct(UserRepository $user, NotificationRepository $notification, PmRepository $pm)
    {
        parent::__construct();

        $this->user = $user;
        $this->notification = $notification;
        $this->pm = $pm;

        $this->breadcrumb->push('Wiadomości prywatne', route('user.pm'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $pm = $this->pm->lengthAwarePaginate($this->userId);
        $messages = PmResource::collection(collect($pm->items()));

        $result = [
            'messages' => $messages->toArray($this->request),
            'per_page' => $pm->perPage(),
            'total' => $pm->total(),
            'current_page' => $pm->currentPage(),
        ];

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return $this->view('user.pm.home')->with($result);
    }

    /**
     * @param Pm $pm
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Pm $pm, Request $request)
    {
        $this->authorize('show', $pm);

        $talk = $this->pm->conversation($this->userId, $pm->author_id, 10, (int) $request->query('offset', 0));
        $messages = PmResource::collection($talk);

        $this->markAllAsRead($pm->author);

        return $this->view('user.pm.show')->with(compact('pm', 'messages'))->with('recipient', $pm->author);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function infinity(Request $request)
    {
        $talk = $this->pm->conversation($this->userId, (int) $request->input('author_id'), 10, (int) $request->query('offset', 0));

        return PmResource::collection($talk);
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function inbox()
    {
        $pm = $this->pm->groupByAuthor($this->userId);

        PmResource::withoutWrapping();

        return PmResource::collection($pm);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function submit(Request $request)
    {
        $this->breadcrumb->push('Napisz wiadomość', route('user.pm.submit'));

        return $this->view('user.pm.show', [
            'recipient' => $request->has('to') ? $this->user->findByName($this->request->input('to')) : new \stdClass(),
            'messages' => []
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        return response($this->getParser()->parse((string) $request->get('text')));
    }

    /**
     * @param PmRequest $request
     * @return PmResource
     */
    public function save(PmRequest $request)
    {
        $recipient = $this->user->findByName($request->input('recipient'));

        $pm = $this->transaction(function () use ($request, $recipient) {
            $result = $this->pm->submit($this->auth, $request->all() + ['author_id' => $recipient->id]);
            $result[Pm::SENTBOX]->assets()->sync($request->input('assets'));

            return $result;
        });

        event(new PmCreated($pm[Pm::INBOX]));

        $recipient->notify(new PmCreatedNotification($pm[Pm::INBOX]));

        PmResource::withoutWrapping();

        return new PmResource($pm[Pm::SENTBOX]);
    }

    /**
     * @param Pm $pm
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(Pm $pm)
    {
        $this->authorize('show', $pm);

        $pm->delete();
    }

    /**
     * @param Pm $pm
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function mark(Pm $pm)
    {
        $this->authorize('show', $pm);

        $this->markAllAsRead($pm->author);
        $this->auth->refresh();

        return response()->json(['count' => $this->auth->pm_unread]);
    }

    /**
     * @param User $author
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function markAllAsRead(User $author)
    {
        $result = $this->pm->getNotRead($this->userId, $author->id);

        foreach ($result as $pm) {
            $this->markAsRead($pm);
        }

        if (count($result)) {
            $this->request->attributes->set('pm_unread', --$this->auth->pm_unread);
        }
    }

    /**
     * @param Pm $pm
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function markAsRead(Pm $pm)
    {
        if ($pm->read_at) {
            return;
        }

        // database trigger will decrease pm counter in "users" table.
        $this->pm->markAsRead($pm->text_id);

        // IF we have unread alert that is connected with that message... then we also have to mark it as read
        if ($this->auth->notifications_unread) {
            $this->notification->markAsReadByModel($this->userId, $pm);
        }

        event(new PmRead($pm));
    }

    /**
     * @param int $authorId
     */
    public function trash($authorId)
    {
        $pm = $this->pm->findWhere(['user_id' => $this->userId, 'author_id' => $authorId]);
        abort_if($pm->count() == 0, 404);

        $this->pm->trash($this->userId, $authorId);
    }

    /**
     * @return \Coyote\Services\Parser\Factories\PmFactory
     */
    private function getParser()
    {
        return app('parser.pm');
    }
}
