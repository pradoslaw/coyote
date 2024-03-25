<?php
namespace Neon\View;

use Neon\ViewModel;

readonly class Navigation implements Item
{
    public function __construct(private ViewModel\Navigation $navigation)
    {
    }

    public function html(callable $h): array
    {
        return [
            $h('header', [
                $h('div', [
                    $this->logo('self-center mr-3.5'),
                    $this->menuItems($h),
                ], 'flex'),
                $h('div', [
                    $this->githubButton($h, 'mr-4'),
                    $this->controls($h),
                ], 'flex'),
            ], 'container mx-auto flex text-[#4E5973] text-sm justify-between mb-4'),
        ];
    }

    private function logo(string $className): string
    {
        return <<<logo
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="24" viewBox="0 0 17 24" fill="none" class="$className">
                <path d="M14.875 12.0501H13.4583C12.2847 12.0501 11.3333 11.0987 11.3333 9.92513V8.50846C11.3333 7.33486 12.2847 6.38346 13.4583 6.38346H14.875C16.0486 6.38346 17 5.43207 17 4.25846V2.8418C17 1.66819 16.0486 0.716797 14.875 0.716797H13.4583C12.2847 0.716797 11.3333 1.66819 11.3333 2.8418V4.25846C11.3333 5.43207 10.3819 6.38346 9.20833 6.38346H7.79167C6.61806 6.38346 5.66667 7.33486 5.66667 8.50846V9.92513C5.66667 11.0987 4.71527 12.0501 3.54167 12.0501H2.125C0.951395 12.0501 0 13.0015 0 14.1751V15.5918C0 16.7654 0.951395 17.7168 2.125 17.7168H9.20833C10.3819 17.7168 11.3333 18.6682 11.3333 19.8418V21.2585C11.3333 22.4321 12.2847 23.3835 13.4583 23.3835H14.875C16.0486 23.3835 17 22.4321 17 21.2585V14.1751C17 13.0015 16.0486 12.0501 14.875 12.0501Z" fill="#00A538"/>
            </svg>
            logo;
    }

    private function menuItems(callable $h): string
    {
        return $h('nav', [
            $h('ul', \array_map(
                fn(string $item, string $href) => $h('li', [
                    $h('a', [$item], [
                        'href'  => $href,
                        'class' => 'px-2 py-4 inline-block',
                    ]),
                ]),
                \array_keys($this->navigation->items),
                $this->navigation->items,
            ),
                'menu-items flex font-semibold'),
        ]);
    }

    private function githubButton(callable $h, string $className): string
    {
        $icon = function (string $class): string {
            return <<<starIcon
<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="$class">
  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
</svg>
starIcon;
        };

        return $h('div', [
            $h('a', [
                $icon('w-4 h-4'),
                $this->navigation->githubName,
            ], [
                'class' => 'name px-2.5 py-1.5 flex gap-x-2',
                'href'  => $this->navigation->githubUrl,
            ]),
            $h('a',
                [$this->navigation->githubStars],
                [
                    'class' => 'stars px-2.5 py-1.5 inline-block',
                    'href'  => $this->navigation->githubStarsUrl,
                ]),
        ], 'github flex border border-solid border-[#E2E2E2] rounded divide-x font-[Helvetica] font-bold text-xs self-center ' . $className);
    }

    private function controls(callable $h): string
    {
        $controls = $this->navigation->controls + ['', ''];
        [$big, $small] = \array_keys($controls);
        return $h('ul', [
            $h('li', [
                $h('a', [$big], ['href' => $controls[$big]]),
            ], 'px-2 py-1.5 self-center rounded bg-[#00A538] text-white'),
            $h('li', [
                $h('a', [$small], ['href' => $controls[$small]]),
            ], 'px-2 py-1.5 self-center'),
        ],
            'controls flex');
    }
}
