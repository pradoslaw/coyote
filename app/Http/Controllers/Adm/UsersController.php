<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Forms\User\AdminForm;
use Coyote\Http\Grids\Adm\UsersGrid;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Boduch\Grid\Source\EloquentSource;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Services\Stream\Activities\Update;
use Coyote\Services\Stream\Objects\Person;
use Coyote\Events\UserWasSaved;

class UsersController extends BaseController
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
        $this->breadcrumb->push('Użytkownicy', route('adm.users'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $this->user->pushCriteria(new WithTrashed());
        $this->user->applyCriteria();

        $grid = $this->gridBuilder()->createGrid(UsersGrid::class);
        $grid->setSource(new EloquentSource($this->user->newQuery()));

        return $this->view('adm.users.home', ['grid' => $grid]);
    }

    /**
     * @param \Coyote\User $user
     * @return \Illuminate\View\View
     */
    public function edit($user)
    {
        $this->breadcrumb->push($user->name, route('adm.users.save', [$user->id]));

        return $this->view('adm.users.save', [
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

        $this->transaction(function () use ($user, $form) {
            $data = $form->all();

            if ($form->get('delete_photo')->isChecked()) {
                $data['photo'] = null;
            }

            // we use forceFill() to fill fields that are NOT in $fillable model's array.
            // we can do that because $form->all() returns only fields in form. $request->all() returns
            // all fields in HTTP POST so it's not secure.
            $user->forceFill(array_except($data, ['submit', 'skills', 'groups', 'delete_photo']))->save();
            $user->skills()->delete();

            if (!empty($data['skills'])) {
                foreach ($data['skills'] as $idx => $skill) {
                    $user->skills()->create($skill + ['order' => $idx + 1]);
                }
            }

            $user->groups()->sync((array) $data['groups']);

            stream(Update::class, new Person($user->toArray()));
            event(new UserWasSaved($user));
        });

        return back()->with('success', 'Zmiany zostały poprawie zapisane');
    }

    /**
     * @param \Coyote\User $user
     * @return \Coyote\Services\FormBuilder\Form
     */
    protected function getForm($user)
    {
        return $this->createForm(AdminForm::class, $user, [
            'url' => route('adm.users.save', [$user->id])
        ]);
    }
}
