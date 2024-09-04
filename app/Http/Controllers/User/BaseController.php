<?php
namespace Coyote\Http\Controllers\User;

use Coyote\Domain\User\MenuItem;
use Coyote\Domain\User\User;
use Coyote\Http\Controllers\Controller;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

abstract class BaseController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb->push('Moje konto', route('user.home'));
    }

    protected function view($view = null, $data = [])
    {
        return parent::view($view, [
            'side_menu' => $this->sideMenu(),
            ...$data,
        ]);
    }

    private function sideMenu(): Builder
    {
        /** @var Menu $menu */
        $menu = app(Menu::class);
        return $menu->make('user.settings', function (Builder $builder) {
            foreach ($this->settingsMenu($this->laravelUser()) as $menuItems) {
                foreach ($menuItems as $menuItem) {
                    $builder->add($menuItem->title, [
                        'id'        => $menuItem->htmlId,
                        'class'     => $menuItem->htmlClass ?? '',
                        'route'     => $menuItem->route,
                        'icon'      => $menuItem->htmlIcon,
                        'subscript' => $menuItem->subscript,
                    ]);
                }
                $builder->divide();
            }
        });
    }

    /**
     * @return MenuItem[]
     */
    public function settingsMenu(User $user): array
    {
        return [
            [
                new MenuItem(
                    'Moje konto',
                    'user.home',
                    htmlId:'btn-start',
                    htmlIcon:'far fa-user'),
                new MenuItem('Twoje umiejętności',
                    'user.skills',
                    htmlId:'btn-skills',
                    htmlIcon:'far fa-address-book'),
            ],
            [
                new MenuItem(
                    'Wiadomości prywatne',
                    'user.pm',
                    subscript:"($user->privateMessagesUnread/$user->privateMessages)",
                    htmlId:'btn-pm',
                    htmlIcon:'far fa-envelope'),
                new MenuItem(
                    'Powiadomienia',
                    'user.notifications',
                    htmlId:'btn-notifies',
                    htmlIcon:'fas fa-bell'),
                new MenuItem(
                    'Oceny moich postów',
                    'user.rates',
                    htmlId:'btn-rates',
                    htmlIcon:'far fa-thumbs-up'),
                new MenuItem(
                    'Statystyki moich postów',
                    'user.stats',
                    htmlId:'btn-stats',
                    htmlIcon:'fa-chart-bar'),
                new MenuItem(
                    'Zaakceptowane odpowiedzi',
                    'user.accepts',
                    htmlId:'btn-accepts',
                    htmlIcon:'fa-check'),
            ],
            [
                new MenuItem(
                    'Obserwowane strony',
                    'user.favorites',
                    htmlId:'btn-favorites',
                    htmlIcon:'far fa-bell'),
                new MenuItem('Zablokowani oraz obserwowani',
                    'user.relations',
                    htmlId:'btn-favorites',
                    htmlIcon:'fa-user-group'),
                new MenuItem('Ustawienia powiadomień',
                    'user.notifications.settings',
                    htmlId:'btn-favorites',
                    htmlIcon:'fa-bell'),
            ],
            [
                new MenuItem('Ustawienia konta',
                    'user.settings',
                    htmlId:'btn-start',
                    htmlIcon:'fa-user-gear'),
                new MenuItem('Zmiana hasła',
                    'user.password',
                    htmlId:'btn-pm',
                    htmlIcon:'fa-unlock-keyhole'),
                new MenuItem('Dostęp',
                    'user.security',
                    htmlId:'btn-notifies',
                    htmlIcon:'fa-door-open'),
                new MenuItem('Tokeny API',
                    'user.tokens',
                    htmlId:'btn-favorites',
                    htmlIcon:'fa-key'),
            ],
            [
                new MenuItem(
                    'Usuń konto',
                    'user.delete',
                    htmlClass:'text-danger',
                    htmlIcon:'fa-trash-can'),
            ],
        ];
    }

    private function laravelUser(): User
    {
        /** @var \Coyote\User $user */
        $user = auth()->user();
        return new User($user->pm, $user->pm_unread);
    }
}
