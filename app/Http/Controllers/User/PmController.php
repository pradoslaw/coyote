<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Events\PmCreated;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Http\Requests\PmRequest;
use Coyote\Http\Resources\PmResource;
use Coyote\Notifications\PmCreatedNotification;
use Coyote\Pm;
use Coyote\Repositories\Contracts\NotificationRepositoryInterface as NotificationRepository;
use Coyote\Repositories\Contracts\PmRepositoryInterface as PmRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * Class PmController
 * @package Coyote\Http\Controllers\User
 */
class PmController extends BaseController
{
    use HomeTrait, MediaFactory;

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

        $recipient = $this->user->find($pm->author_id, ['id', 'name']);

        $this->markAllAsRead($pm->author_id);

        return $this->view('user.pm.show')->with(compact('pm', 'messages', 'recipient'));
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
     * Get last 10 conversations
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function inbox()
    {
        $pm = $this->pm->groupByAuthor($this->userId);

        return response()->json([
            'pm' => PmResource::collection($pm)
        ]);
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
            return $this->pm->submit($this->auth, $request->all() + ['author_id' => $recipient->id]);
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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function mark(Pm $pm)
    {
        $this->authorize('show', $pm);

        $this->markAsRead($pm);
    }

    /**
     * @param int $authorId
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function markAllAsRead(int $authorId)
    {
        $result = $this->pm->getUnreadIds($this->userId, $authorId);

        foreach ($result as $pm) {
            $this->markAsRead($pm);
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

        $this->request->attributes->set('pm_unread', --$this->auth->pm_unread);
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function paste()
    {
        $input = file_get_contents("php://input");

        $validator = $this->getValidationFactory()->make(
            ['length' => strlen($input)],
            ['length' => 'max:' . config('filesystems.upload_max_size') * 1024 * 1024]
        );

        $this->validateWith($validator);

        // put to s3
        $media = $this->getMediaFactory()->make('screenshot')->put(file_get_contents('data://' . substr($input, 7)));

        $filesystem = app('filesystem')->disk('local_fs');
        $mime = null;

        try {
            $filesystem->put($media->getFilename(), $media->get());

            $mime = (MimeTypeGuesser::getInstance())->guess(storage_path('app/' . $media->getFilename()));
        } finally {
            $filesystem->delete($media->getFilename());
        }

        return response()->json([
            'size'      => $media->size(),
            'suffix'    => 'png',
            'name'      => $media->getName(),
            'file'      => $media->getFilename(),
            'mime'      => $mime,
            'url'       => (string) $media->url()
        ]);
    }

    /**
     * @return \Coyote\Services\Parser\Factories\PmFactory
     */
    private function getParser()
    {
        return app('parser.pm');
    }
}
