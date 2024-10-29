<?php
namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\EloquentSource;
use Carbon\Carbon;
use Coyote\Domain\Administrator\User\Store\UserStore;
use Coyote\Domain\Administrator\User\View\Activity;
use Coyote\Domain\Administrator\User\View\Navigation;
use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Survey\Clock;
use Coyote\Domain\Survey\GuestSurvey;
use Coyote\Events\UserDeleted;
use Coyote\Events\UserSaved;
use Coyote\Http\Forms\User\AdminForm;
use Coyote\Http\Grids\Adm\UsersGrid;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\Guest;
use Coyote\Services\Stream\Activities\Update;
use Coyote\Services\Stream\Objects\Person;
use Coyote\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsersController extends BaseController
{
    public function __construct(private UserRepository $user)
    {
        parent::__construct();
        $this->breadcrumb->push('Użytkownicy', route('adm.users'));
    }

    public function index(): View
    {
        $this->user->pushCriteria(new WithTrashed());
        $this->user->applyCriteria();
        $grid = $this->gridBuilder()->createGrid(UsersGrid::class);
        $grid->setSource(new EloquentSource($this->user->newQuery()));
        return $this->view('adm.users.home', ['grid' => $grid]);
    }

    public function show(User $user): View
    {
        $this->breadcrumb->push("@$user->name", route('adm.users.show', [$user->id]));
        $daysAgo = $this->daysAgo($this->request);
        $store = new UserStore($user, Carbon::now()->subDays($daysAgo));
        return $this->view('adm.users.show', [
            'accountCreated' => new Date($user->created_at, Carbon::now()),
            'navigation'     => new Navigation($user->id, $user->name),
            'activity'       => new Activity(
                $store->postsCategoriesStatistic(),
                $store->postsCategoriesStatisticLikes(),
                $store->deleteReasons(),
                $store->reportReasons(),
                $store->postStats(),
            ),
        ]);
    }

    private function daysAgo(Request $request): int
    {
        $key = $request->query('last') ?? 'default';
        $map = [
            'day'     => 1,
            'week'    => 7,
            'month'   => 31,
            'year'    => 365,
            'default' => 40 * 365,
        ];
        return $map[$key] ?? $map['default'];
    }

    public function edit(User $user): View
    {
        $this->breadcrumb->push("@$user->name", route('adm.users.show', [$user->id]));
        $this->breadcrumb->push('Ustawienia konta', route('adm.users.save', [$user->id]));
        [$userSettings, $surveyLog] = $this->userSettings($user);
        return $this->view('adm.users.save', [
            'user'         => $user,
            'form'         => $this->getForm($user),
            'userSettings' => $this->formatJson($userSettings),
            'surveyLog'    => $this->formatJson($surveyLog),
        ]);
    }

    private function userSettings(User $user): array
    {
        $userSettings = \array_diff_key($user->guest?->settings ?? [], ['surveyLog' => null]);
        $surveyLog = $user->guest?->settings['surveyLog'] ?? null;
        return [$userSettings, $surveyLog];
    }

    protected function getForm(User $user): Form
    {
        return $this->createForm(AdminForm::class, $user, [
            'url' => route('adm.users.save', [$user->id]),
        ]);
    }

    private function formatJson(?array $object): string
    {
        return \json_encode($object, \JSON_PRETTY_PRINT);
    }

    public function save(User $user, Clock $clock): RedirectResponse
    {
        $form = $this->getForm($user);
        $form->validate();

        $this->transaction(function () use ($clock, $user, $form) {
            $data = $form->all();
            if ($form->get('delete_photo')->isChecked()) {
                $data['photo'] = null;
            }

            // we use forceFill() to fill fields that are NOT in $fillable model's array.
            // we can do that because $form->all() returns only fields in form. $request->all() returns
            // all fields in HTTP POST so it's not secure.
            $user->forceFill(array_except($data, ['submit', 'skills', 'groups', 'delete_photo']))->save();

            $user->groups()->sync((array)$data['groups']);
            stream(Update::class, new Person($user));
            event($user->deleted_at ? new UserDeleted($user) : new UserSaved($user));

            if ($this->request->has('local-settings-action')) {
                $action = $this->request->get('local-settings-action');
                $value = $this->request->get('local-settings-value');
                $guest = new Guest($user->guest_id);
                $survey = new GuestSurvey($guest, $clock);

                if ($action === 'survey-none') {
                    $survey->setState('survey-none');
                }
                if ($action === 'survey-invited') {
                    $survey->setState('survey-invited');
                }
                if ($action === 'survey-clear-log') {
                    $survey->clearLog();
                }
                if ($action === 'review-add') {
                    $posts = $guest->getSetting('postsToReview', []);
                    $guest->setSetting('postsToReview', 
                        \array_values(\array_unique([...$posts, (int)$value])));
                }
                if ($action === 'review-clear') {
                    $guest->setSetting('postsToReview', []);
                    $guest->setSetting('postsReviewed', []);
                }
            }
        });

        return back()->with('success', 'Zmiany zostały zapisane.');
    }
}
