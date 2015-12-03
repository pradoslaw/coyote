<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as Alert;
use Coyote\Repositories\Contracts\PmRepositoryInterface as Pm;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Illuminate\Http\Request;
use Carbon;

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

    public function index()
    {
        $this->breadcrumb->push('Wiadomości prywatne', route('user.pm'));

        $pm = $this->pm->paginate(auth()->user()->id);

        return parent::view('user.pm.home')->with(compact('pm'));
    }

    public function show($id)
    {
        $pm = $this->pm->findOrFail($id, ['user_id', 'root_id']);
        if ($pm->user_id !== auth()->user()->id) {
            abort(500);
        }
        $talk = $this->pm->talk(auth()->user()->id, $pm->root_id);
        $parser = app()->make('Parser\Pm');

        foreach ($talk as &$row) {
            $row['text'] = $parser->parse($row['text']);
        }

        return parent::view('user.pm.show')->with(compact('pm', 'talk'));
    }

    public function submit()
    {
        $this->breadcrumb->push('Wiadomości prywatne', route('user.pm'));
        $this->breadcrumb->push('Napisz wiadomość', route('user.pm.submit'));

        return parent::view('user.pm.submit');
    }

    public function save(Request $request)
    {
        $this->validate($request, [
            'author'             => 'required|username|exists:users,name',
            'text'               => 'required',
            'root_id'            => 'sometimes|exists:pm'
        ]);

        $pm = $this->pm->submit(auth()->user(), $request);
        return redirect()->route('user.pm.show', [$pm->id])->with('success', 'Wiadomość została wysłana');
    }
}
