<?php
namespace Coyote\Domain\User;

class UserMenu
{
    /**
     * @return MenuItem[]
     */
    public function profileNavigation(User $user): array
    {
        if ($user->loggedIn) {
            return [
                new MenuItem('Moje konto', 'user.home'),
                new MenuItem('Ustawienia', 'user.settings'),
            ];
        }
        return [];
    }

    /**
     * @return MenuItem[]
     */
    public function settingsMenu(): array
    {
        return [
            new MenuItem('Podstawowa konfiguracja',
                'user.settings',
                htmlId:'btn-start',
                htmlIcon:'fa-cog'),
            new MenuItem('Umiejętności',
                'user.skills',
                htmlId:'btn-visits',
                htmlIcon:'fa-wrench'),
            new MenuItem('Bezpieczeństwo',
                'user.security',
                htmlId:'btn-notifies',
                htmlIcon:'fa-lock'),
            new MenuItem('Zmiana hasła',
                'user.password',
                htmlId:'btn-pm',
                htmlIcon:'fa-key'),
            new MenuItem('Ustawienia powiadomień',
                'user.notifications.settings',
                htmlId:'btn-favorites',
                htmlIcon:'fa-bell'),
            new MenuItem('Zablokowani oraz obserwowani',
                'user.relations',
                htmlId:'btn-favorites',
                htmlIcon:'fa-user-slash'),
            new MenuItem('Tokeny API',
                'user.tokens',
                htmlId:'btn-favorites',
                htmlIcon:'fa-key'),
        ];
    }

    /**
     * @return MenuItem[]
     */
    public function accountMenu(User $user): array
    {
        return [
            new MenuItem(
                'Start',
                'user.home',
                htmlId:'btn-start',
                htmlIcon:'fa-map-marker',
            ),
            new MenuItem(
                'Powiadomienia',
                'user.notifications',
                htmlId:'btn-notifies',
                htmlIcon:'fa-bell'),
            new MenuItem(
                'Wiadomości prywatne',
                'user.pm',
                subscript:"($user->privateMessagesUnread/$user->privateMessages)",
                htmlId:'btn-pm',
                htmlIcon:'fa-envelope'),
            new MenuItem(
                'Ulubione i obserwowane strony',
                'user.favorites',
                htmlId:'btn-favorites',
                htmlIcon:'fa-heart'),
            new MenuItem(
                'Oceny moich postów',
                'user.rates',
                htmlId:'btn-rates',
                htmlIcon:'fa-star-half'),
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
            new MenuItem(
                'Usuń konto',
                'user.delete',
                htmlClass:'text-danger',
                htmlIcon:'fa-trash-alt'),
        ];
    }
}
