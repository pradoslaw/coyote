<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as Alert;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as Session;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;

class AlertsController extends Controller
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
     * @param User $user
     * @param Alert $alert
     */
    public function __construct(User $user, Alert $alert)
    {
        parent::__construct();

        $this->user = $user;
        $this->alert = $alert;
    }

    /**
     * @param Session $session
     * @return $this
     */
    public function index(Session $session)
    {
        $this->breadcrumb->push('Powiadomienia', route('user.alerts'));

        $alerts = $this->alert->paginate(auth()->user()->id);
        $session = $session->findBy('user_id', auth()->user()->id, ['created_at']);

        $markId = [];
        foreach ($alerts as $alert) {
            if (!$alert->read_at) {
                $markId[] = $alert->id;
            }
        }

        if ($markId) {
            $this->alert->markAsRead($markId);
        }

        return parent::view('user.alerts')->with(compact('alerts', 'session'));
    }
}
