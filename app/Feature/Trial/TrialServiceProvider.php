<?php
namespace Coyote\Feature\Trial;

use Coyote\Domain\Settings\UserTheme;
use Illuminate\Contracts\View\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;

class TrialServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Factory $view */
        $view = $this->app['view'];
        $this->add($view);
    }

    private function add(Factory $viewFactory): void
    {
        $viewFactory->composer('home', $this->addViewField(...));
    }

    private function addViewField(View $view): void
    {
        $view->with([
            'survey' => [
                'trial'         => [
                    'title'       => 'Wygląd strony głównej.',
                    'reason'      => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras velit metus, egestas id facilisis vel, consectetur sit amet magna. Praesent auctor arcu augue, ut efficitur dui rhoncus et.',
                    'solution'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras velit metus, egestas id facilisis vel, consectetur sit amet magna. Praesent auctor arcu augue, ut efficitur dui rhoncus et. Donec tempus dapibus justo a faucibus.',
                    'dueDateTime' => '2024-11-15 16:00:00',
                    'imageLight'  => [
                        'imageLegacy' => '/img/survey/postCommentStyle/legacy.light.png',
                        'imageModern' => '/img/survey/postCommentStyle/modern.light.png',
                    ],
                    'imageDark'   => [
                        'imageLegacy' => '/img/survey/postCommentStyle/legacy.dark.png',
                        'imageModern' => '/img/survey/postCommentStyle/modern.dark.png',
                    ],
                ],
                'userSession'   => [
                    'stage'      => 'stage-none',
                    'choice'     => 'choice-modern', 'choice-legacy', 'choice-pending',
                    'badgeLong'  => true,
                    'assortment' => 'assortment-legacy', // 'assortment-modern'
                ],
                'userThemeDark' => $this->userTheme()->isThemeDark(),
            ],
        ]);
    }

    private function userTheme(): UserTheme
    {
        /** @var UserTheme $theme */
        $theme = $this->app[UserTheme::class];
        return $theme;
    }
}
