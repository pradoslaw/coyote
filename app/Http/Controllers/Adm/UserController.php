<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Grid\Source\Eloquent;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    private $user;

    public function __construct(UserRepository $user)
    {
        parent::__construct();

        $this->user = $user;
        $this->breadcrumb->push('Użytkownicy', route('adm.user'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $grid = $this->getGrid();

        $grid->setSource(new Eloquent($this->user->newQuery()));

        // asdadasdas
        $grid->addColumn('id', 'text');
        $grid->addColumn('name', 'text', ['label' => 'Nazwa użytkownika']);
        $grid->addColumn('email', 'text', ['label' => 'E-mail']);
        $grid->addColumn('created_at', 'text', ['label' => 'Data rejestracji']);
        $grid->addColumn('visited_at', 'text', ['label '=> 'Data ost. wizyty']);
        $grid->addColumn('is_active', 'text', ['label' => 'Aktywny']);
        $grid->addColumn('is_blocked', 'text', ['label' => 'Zablokowany']);
        $grid->addColumn('ip', 'text', ['label' => 'IP']);

        return $this->view('adm.user.home', ['grid' => $grid->render()]);
    }
}
