<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Forms\User\SettingsForm;
use Coyote\Http\Grids\Adm\UsersGrid;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Grid\Source\Eloquent;
use Coyote\Services\Stream\Activities\Update;
use Coyote\Services\Stream\Objects\Person;
use Coyote\Events\UserWasSaved;

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
        $grid = $this->getGrid()->createGrid(UsersGrid::class);
        $grid->setSource(new Eloquent($this->user->newQuery()));

        return $this->view('adm.user.home', ['grid' => $grid]);
    }

    /**
     * @param \Coyote\User $user
     * @return \Coyote\Services\FormBuilder\Form
     */
    public function edit($user)
    {
        $this->breadcrumb->push($user->name, route('adm.user.save', [$user->id]));

        return $this->view('adm.user.save', [
            'user' => $user,
            'form' => $this->createForm(SettingsForm::class, $user, [
                'url' => route('adm.user.save', [$user->id])
            ])
        ]);
    }

    /**
     * @param \Coyote\User $user
     * @param SettingsForm $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($user, SettingsForm $form)
    {
        $this->transaction(function () use ($user, $form) {
            $user->fill($form->getRequest()->all())->save();
            stream(Update::class, new Person());

            event(new UserWasSaved($user->id));
        });

        return back()->with('success', 'Zmiany zostały poprawie zapisane');
    }
}
