<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as Alert;
use Coyote\Repositories\Contracts\PmRepositoryInterface as Pm;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Alert\Providers\Pm as Alert_Pm;
use Illuminate\Http\Request;
use Guzzle\Http\Mimetypes;
use Carbon;

/**
 * Class PmController
 * @package Coyote\Http\Controllers\User
 */
class PmController extends Controller
{
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
     * @return $this
     */
    public function index()
    {
        $this->breadcrumb->push('Wiadomości prywatne', route('user.pm'));

        $pm = $this->pm->paginate(auth()->user()->id);
        $parser = app()->make('Parser\Pm');

        foreach ($pm as &$row) {
            $row->text = $parser->parse($row->text);
        }

        return parent::view('user.pm.home')->with(compact('pm'));
    }

    /**
     * Show conversation
     *
     * @param int $id
     * @param Request $request
     * @return $this
     */
    public function show($id, Request $request)
    {
        $this->breadcrumb->push('Wiadomości prywatne', route('user.pm'));

        $pm = $this->pm->find($id, ['user_id', 'root_id', 'id']);

        if (!$pm) {
            return redirect()->route('user.pm');
        }
        if ($pm->user_id !== auth()->user()->id) {
            abort(500);
        }
        $talk = $this->pm->talk(auth()->user()->id, $pm->root_id, 10, $request->query('offset', 0));
        $parser = app()->make('Parser\Pm');

        foreach ($talk as &$row) {
            $row['text'] = $parser->parse($row['text']);

            // we have to mark this message as read
            if (!$row['read_at'] && $row['folder'] == \Coyote\Pm::INBOX) {
                $this->pm->markAsRead($row['id']);
            }

            // IF we have unread alert that is connected with that message... then we also have to mark it as read
            if (auth()->user()->alerts_unread) {
                $this->alert->markAsReadByUrl(auth()->user()->id, route('user.pm.show', [$row['id']], false));
            }
        }

        if ($request->ajax()) {
            return view('user.pm.infinite')->with('talk', $talk);
        }

        return parent::view('user.pm.show')->with(compact('pm', 'talk'));
    }

    /**
     * Get last 10 conversations
     *
     * @return $this
     */
    public function ajax()
    {
        $pm = $this->pm->takeForUser(auth()->user()->id);

        return view('user.pm.ajax')->with(compact('pm'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function submit()
    {
        $this->breadcrumb->push('Wiadomości prywatne', route('user.pm'));
        $this->breadcrumb->push('Napisz wiadomość', route('user.pm.submit'));

        return parent::view('user.pm.submit');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        $parser = app()->make('Parser\Pm');
        return response($parser->parse($request->get('text')));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $this->validate($request, [
            'author'             => 'required|username|exists:users,name',
            'text'               => 'required',
            'root_id'            => 'sometimes|exists:pm'
        ]);

        return \DB::transaction(function () use ($request) {
            $user = auth()->user();
            $pm = $this->pm->submit($user, $request);

            (new Alert_Pm($this->alert))->with([
                'user_id'     => $pm->author_id,
                'sender_id'   => $user->id,
                'sender_name' => $user->name,
                'subject'     => excerpt($request->get('text'), 48),
                'url'         => route('user.pm.show', [$pm->id - 1], false)
            ])->notify();

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
        if ($pm->user_id !== auth()->user()->id) {
            abort(500);
        }

        $pm->delete();
        return back()->with('success', 'Wiadomość poprawnie usunięta');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function paste()
    {
        $input = file_get_contents("php://input");
        if (strlen($input) > (config('filesystems.upload_max_size') * 1024 * 1024)) {
            abort(500, 'File is too big');
        }

        $fileName = uniqid() . '.png';
        $path = public_path('storage/pm/' . $fileName);

        file_put_contents($path, file_get_contents('data://' . substr($input, 7)));
        $mime = new Mimetypes();

        return response()->json([
            'size' => filesize($path),
            'suffix' => 'png',
            'name' => $fileName,
            'file' => $fileName,
            'mime'  => $mime->fromFilename($fileName),
            'url' => cdn('storage/pm/' . $fileName)
        ]);
    }
}
