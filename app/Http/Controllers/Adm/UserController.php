<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Grid\Source\Eloquent;

class UserController extends BaseController
{
    /**
     * @var UserRepository
     */
    private $user;

    /**
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        parent::__construct();

        $this->user = $user;
        $this->breadcrumb->push('Użytkownicy', route('adm.user'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $grid = $this->getGrid();

        $grid
            ->setSource(new Eloquent($this->user->newQuery()))
            ->setDefaultOrder('id', 'desc')
            ->addColumn('id', 'text', ['label' => 'ID', 'sortable' => true])
            ->addColumn('name', 'text', ['label' => 'Nazwa użytkownika', 'sortable' => true])
            ->addColumn('email', 'text', ['label' => 'E-mail'])
            ->addColumn('created_at', 'text', ['label' => 'Data rejestracji'])
            ->addColumn('visited_at', 'text', ['label '=> 'Data ost. wizyty', 'sortable' => true])
            ->addColumn('is_active', 'text', ['label' => 'Aktywny'])
            ->addColumn('is_blocked', 'text', ['label' => 'Zablokowany'])
            ->addColumn('ip', 'text', ['label' => 'IP']);

        return $this->view('adm.user.home', ['grid' => $grid->render()]);
    }
}
