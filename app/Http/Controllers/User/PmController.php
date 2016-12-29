<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Events\PmWasSent;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as AlertRepository;
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
     * @var AlertRepository
     */
    private $alert;

    /**
     * @var PmRepository
     */
    private $pm;

    /**
     * @param UserRepository $user
     * @param AlertRepository $alert
     * @param PmRepository $pm
     */
    public function __construct(UserRepository $user, AlertRepository $alert, PmRepository $pm)
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

        $talk = $this->pm->talk($this->userId, $pm->root_id, 10, (int) $request->query('offset', 0));
        $parser = $this->getParser();

        foreach ($talk as &$row) {
            $row['text'] = $parser->parse($row['text']);

            // we have to mark this message as read
            if (!$row['read_at'] && $row['folder'] == \Coyote\Pm::INBOX) {
                // database trigger will decrease pm counter in "users" table.
                $this->pm->markAsRead($row['text_id']);
                $this->auth->pm_unread--;

                // IF we have unread alert that is connected with that message... then we also have to mark it as read
                if ($this->auth->alerts_unread) {
                    $this->alert->markAsReadByUrl($this->userId, route('user.pm.show', [$row['id']], false));
                }
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
            'recipient'          => 'required|username|user_exist',
            'text'               => 'required',
            'root_id'            => 'sometimes|exists:pm'
        ]);

        $validator->after(function (Validator $validator) use ($request) {
            if (mb_strtolower($request->get('recipient')) === mb_strtolower($this->auth->name)) {
                $validator->errors()->add('recipient', trans('validation.custom.recipient.different'));
            }
        });

        $this->validateWith($validator);

        return $this->transaction(function () use ($request) {
            $recipient = $this->user->findByName($request->get('recipient'));

            $pm = $this->pm->submit($this->auth, $request->all() + ['author_id' => $recipient->id]);

            $excerpt = excerpt($text = $this->getParser()->parse($request->get('text')));

            // we need to send notification to recipient
            app('alert.pm')->with([
                'user_id'     => $pm->author_id,
                'sender_id'   => $this->auth->id,
                'sender_name' => $this->auth->name,
                'subject'     => $excerpt,
                'text'        => $text,
                'url'         => route('user.pm.show', [$pm->id - 1], false)
            ])->notify();

            // broadcast event: we can use it to show message in real time
            event(new PmWasSent($pm->author_id, $this->auth->id, $this->auth->name, $excerpt));

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

        return $this->transaction(function () use ($pm) {
            $pm->delete();

            $redirect = redirect();
            $to = $this->pm->findWhere(['root_id' => $pm->root_id, 'user_id' => $this->userId], ['id']);

            $redirect = $to->count() ? $redirect->route('user.pm.show', [$to->first()->id]) : $redirect->route('user.pm');

            return $redirect->with('success', 'Wiadomość poprawnie usunięta');
        });
    }

    /**
     * @param string $rootId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function trash($rootId)
    {
        $pm = $this->pm->findWhere(['user_id' => $this->userId, 'root_id' => $rootId]);
        abort_if($pm->count() == 0, 404);

        $this->pm->trash($this->userId, $rootId);

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

        $media = $this->getMediaFactory('screenshot')->put(file_get_contents('data://' . substr($input, 7)));
        $mime = MimeTypeGuesser::getInstance();

        return response()->json([
            'size'      => $media->size(),
            'suffix'    => 'png',
            'name'      => $media->getName(),
            'file'      => $media->getFilename(),
            'mime'      => $mime->guess($media->path()),
            'url'       => $media->url()
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
