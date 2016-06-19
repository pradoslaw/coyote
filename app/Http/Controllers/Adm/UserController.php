<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Forms\User\AdminForm;
use Coyote\Http\Grids\Adm\UsersGrid;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Grid\Source\EloquentDataSource;
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
        $grid->setSource(new EloquentDataSource($this->user->newQuery()));

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
            'form' => $this->getForm($user)
        ]);
    }

    /**
     * @param \Coyote\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($user)
    {
        $form = $this->getForm($user);
        $form->validate();

        $data = $form->all();

        $this->transaction(function () use ($user, $data) {
            $user->fill($data)->save();
            $user->skills()->delete();

            if (!empty($data['skills'])) {
                foreach ($data['skills'] as $idx => $skill) {
                    $user->skills()->create($skill + ['order' => $idx + 1]);
                }
            }

            stream(Update::class, new Person());
            event(new UserWasSaved($user->id));
        });

        return back()->with('success', 'Zmiany zostały poprawie zapisane');
    }

    protected function getForm($user)
    {
        return $this->createForm(AdminForm::class, $user, [
            'url' => route('adm.user.save', [$user->id])
        ]);
    }
}
