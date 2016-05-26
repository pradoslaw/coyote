<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Events\PmWasSent;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as Alert;
use Coyote\Repositories\Contracts\PmRepositoryInterface as Pm;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Illuminate\Http\Request;
use Guzzle\Http\Mimetypes;

/**
 * Class PmController
 * @package Coyote\Http\Controllers\User
 */
class PmController extends BaseController
{
    use HomeTrait, MediaFactory;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Alert
     */
    private $alert;

    /**
     * @var Pm
     */
    private $pm;

    /**
     * @param User $user
     * @param Alert $alert
     * @param Pm $pm
     */
    public function __construct(User $user, Alert $alert, Pm $pm)
    {
        parent::__construct();

        $this->user = $user;
        $this->alert = $alert;
        $this->pm = $pm;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Wiadomości prywatne', route('user.pm'));

        $pm = $this->pm->paginate($this->userId);
        $parser = app('parser.pm');

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

        $talk = $this->pm->talk($this->userId, $pm->root_id, 10, (int) $request->query('offset', 0));
        $parser = app('parser.pm');

        foreach ($talk as &$row) {
            $row['text'] = $parser->parse($row['text']);

            // we have to mark this message as read
            if (!$row['read_at'] && $row['folder'] == \Coyote\Pm::INBOX) {
                $this->pm->markAsRead($row['id']);
            }

            // IF we have unread alert that is connected with that message... then we also have to mark it as read
            if (auth()->user()->alerts_unread) {
                $this->alert->markAsReadByUrl($this->userId, route('user.pm.show', [$row['id']], false));
            }
        }

        if ($request->ajax()) {
            return view('user.pm.infinite')->with('talk', $talk);
        }

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
        $parser = app('parser.pm');

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
        $parser = app('parser.pm');
        return response($parser->parse($request->get('text')));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $validator = $this->getValidationFactory()->make($request->all(), [
            'recipient'          => 'required|username|user_exist',
            'text'               => 'required',
            'root_id'            => 'sometimes|exists:pm'
        ]);

        $validator->after(function ($validator) use ($request) {
            if (mb_strtolower($request->get('recipient')) === mb_strtolower(auth()->user()->name)) {
                $validator->errors()->add('recipient', trans('validation.custom.recipient.different'));
            }
        });

        $this->validateWith($validator);

        return $this->transaction(function () use ($request) {
            $recipient = $this->user->findByName($request->get('recipient'));

            $user = auth()->user();
            $pm = $this->pm->submit($user, $request->all() + ['author_id' => $recipient->id]);

            $excerpt = excerpt($request->get('text'));

            // we need to send notification to recipient
            app('alert.pm')->with([
                'user_id'     => $pm->author_id,
                'sender_id'   => $user->id,
                'sender_name' => $user->name,
                'subject'     => $excerpt,
                'url'         => route('user.pm.show', [$pm->id - 1], false)
            ])->notify();

            // broadcast event: we can use it to show message in real time
            event(new PmWasSent($pm->author_id, $user->id, $user->name, $excerpt));

            // redirect to sent message...
            return redirect()->route('user.pm.show', [$pm->id])->with('success', 'Wiadomość została wysłana');
        });
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $pm = $this->pm->findOrFail($id, ['id', 'user_id', 'root_id']);
        $this->authorize('show', $pm);

        $pm->delete();
        return back()->with('success', 'Wiadomość poprawnie usunięta');
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

        $media = $this->getMediaFactory('screenshot')->put(file_get_contents('data://' . substr($input, 7)));
        $mime = new Mimetypes();

        return response()->json([
            'size' => $media->size(),
            'suffix' => 'png',
            'name' => $media->getName(),
            'file' => $media->getFilename(),
            'mime'  => $mime->fromFilename($media->path()),
            'url' => $media->url()
        ]);
    }
}
