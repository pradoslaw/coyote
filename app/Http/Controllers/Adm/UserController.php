<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Illuminate\Http\Request;
use ViewComponents\Eloquent\EloquentDataProvider;
use ViewComponents\Grids\Component\Column;
use ViewComponents\Grids\Component\ColumnSortingControl;
use ViewComponents\Grids\Grid;
use ViewComponents\ViewComponents\Component\Control\PaginationControl;
use ViewComponents\ViewComponents\Customization\CssFrameworks\BootstrapStyling;
use ViewComponents\ViewComponents\Input\InputSource;

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
        $input = new InputSource($request->all());

        $provider = new EloquentDataProvider($this->user->newQuery());
        $grid = new Grid($provider, [
            new Column('id'),
            new Column('name', 'Nazwa użytkownika'),
            new Column('email', 'E-mail'),
            new Column('created_at', 'Data rejestracji'),
            new Column('visited_at', 'Data ost. wizyty'),
            new Column('is_active', 'Aktywny'),
            new Column('is_blocked', 'Zablokowany'),
            new Column('ip', 'IP'),

            new ColumnSortingControl('id', $input->option('sort')),
            new ColumnSortingControl('name', $input->option('sort')),
            new ColumnSortingControl('is_active', $input->option('sort')),
            new PaginationControl($input->option('page', 1), 5)
        ]);

        $customization = new BootstrapStyling();
        $customization->apply($grid);

        return $this->view('adm.user.home', ['grid' => $grid->render()]);
    }
}
