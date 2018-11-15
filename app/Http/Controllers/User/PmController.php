<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Factories\MediaFactory;
use Coyote\Notifications\PmCreatedNotification;
use Coyote\Repositories\Contracts\NotificationRepositoryInterface as NotificationRepository;
use Coyote\Repositories\Contracts\PmRepositoryInterface as PmRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Illuminate\Validation\Validator;
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

        $this->middleware(function (Request $request, $next) {
            $request->attributes->set('preview_url', route('user.pm.preview'));

            return $next($request);
        });
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Wiadomości prywatne', route('user.pm'));

        $pm = $this->pm->paginate($this->userId);
        $parser = $this->getParser();

        foreach ($pm as &$row) {
            $row->text = $parser->parse($row->text);
        }

        return $this->view('user.pm.home')->with(compact('pm'));
    }

    /**
     * Show conversation
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show($id, Request $request)
    {
        $this->breadcrumb->push('Wiadomości prywatne', route('user.pm'));

        $pm = $this->pm->findOrFail($id, ['user_id', 'author_id', 'root_id', 'id']);
        $this->authorize('show', $pm);

        $talk = $this->pm->talk($this->userId, $pm->author_id, 10, (int) $request->query('offset', 0));
        $parser = $this->getParser();

        foreach ($talk as &$row) {
            $row['text'] = $parser->parse($row['text']);

            // we have to mark this message as read
            if (!$row['read_at'] && $row['folder'] == \Coyote\Pm::INBOX) {
                // database trigger will decrease pm counter in "users" table.
                $this->pm->markAsRead($row['text_id']);
                $this->auth->pm_unread--;

                // IF we have unread alert that is connected with that message... then we also have to mark it as read
                if ($this->auth->notifications_unread) {
                    $this->notification->markAsReadByUrl($this->userId, route('user.pm.show', [$row['id']], false));
                }
            }
        }

        if ($request->ajax()) {
            return view('user.pm.infinite')->with('talk', $talk);
        }

        $this->request->attributes->set('infinity_url', route('user.pm.show', [$id]));

        $recipient = $this->user->find($pm->author_id, ['name']);
        return $this->view('user.pm.show')->with(compact('pm', 'talk', 'recipient'));
    }

    /**
     * Get last 10 conversations
     *
     * @return \Illuminate\View\View
     */
    public function ajax()
    {
        $parser = $this->getParser();

        $pm = $this->pm->takeForUser($this->userId);
        foreach ($pm as &$row) {
            $row->text = $parser->parse($row->text);
        }

        return view('user.pm.ajax')->with(compact('pm'));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function submit()
    {
        $this->breadcrumb->push('Wiadomości prywatne', route('user.pm'));
        $this->breadcrumb->push('Napisz wiadomość', route('user.pm.submit'));

        return $this->view('user.pm.submit');
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        return response($this->getParser()->parse($request->get('text')));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $validator = $this->getValidationFactory()->make($request->all(), [
            'recipient'          => 'required|user_exist',
            'text'               => 'required',
            'root_id'            => 'sometimes|exists:pm'
        ]);

        $validator->after(function (Validator $validator) use ($request) {
            if (mb_strtolower($request->get('recipient')) === mb_strtolower($this->auth->name)) {
                $validator->errors()->add('recipient', trans('validation.custom.recipient.different'));
            }
        });

        $this->validateWith($validator);
        $recipient = $this->user->findByName($request->get('recipient'));

        $pm = $this->transaction(function () use ($request, $recipient) {
            return $this->pm->submit($this->auth, $request->all() + ['author_id' => $recipient->id]);
        });

        $recipient->notify(new PmCreatedNotification($pm));

        // redirect to sent message...
        return redirect()->route('user.pm.show', [$pm->id])->with('success', 'Wiadomość została wysłana');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $pm = $this->pm->findOrFail($id, ['id', 'user_id', 'author_id']);
        $this->authorize('show', $pm);

        return $this->transaction(function () use ($pm) {
            $pm->delete();

            $redirect = redirect();
            $to = $this->pm->findWhere(['author_id' => $pm->author_id, 'user_id' => $this->userId], ['id']);

            $redirect = $to->count() ? $redirect->route('user.pm.show', [$to->first()->id]) : $redirect->route('user.pm');

            return $redirect->with('success', 'Wiadomość poprawnie usunięta');
        });
    }

    /**
     * @param int $authorId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function trash($authorId)
    {
        $pm = $this->pm->findWhere(['user_id' => $this->userId, 'author_id' => $authorId]);
        abort_if($pm->count() == 0, 404);

        $this->pm->trash($this->userId, $authorId);

        return redirect()->route('user.pm')->with('success', 'Wątek został bezpowrotnie usunięty.');
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

        $media = $this->getMediaFactory()->make('screenshot')->put(file_get_contents('data://' . substr($input, 7)));
        $mime = MimeTypeGuesser::getInstance();

        return response()->json([
            'size'      => $media->size(),
            'suffix'    => 'png',
            'name'      => $media->getName(),
            'file'      => $media->getFilename(),
            'mime'      => $mime->guess($media->path()),
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
