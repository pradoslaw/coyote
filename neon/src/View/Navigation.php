<?php
namespace Neon\View;

use Neon\ViewModel;

readonly class Navigation
{
    public function __construct(private ViewModel\Navigation $navigation)
    {
    }

    public function html(callable $h): array
    {
        return [
            $h('header', [
                $h('nav', [
                    $h('ul', \array_map(
                        fn(string $item) => $h('li', [$item]),
                        $this->navigation->items)),
                ]),
                $h('div', [
                    $this->navigation->githubName,
                    $h('span', [
                        $this->navigation->githubStars,
                    ], 'stars'),
                ], 'github'),
                $h('ul', \array_map(
                    fn(string $item) => $h('li', [$item]),
                    $this->navigation->controls)),
            ]),
        ];
    }
}
