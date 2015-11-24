<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as Alert;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;

class AlertsController extends Controller
{
    private $user;
    private $alert;

    public function __construct(User $user, Alert $alert)
    {
        parent::__construct();

        $this->user = $user;
        $this->alert = $alert;
    }

    public function index()
    {
        $this->breadcrumb->push('Powiadomienia', route('user.alerts'));

        $this->alert->takeForUser(auth()->user()->id, 20);

        return parent::view('user.alerts');
    }
}
