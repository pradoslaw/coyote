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
                $this->menuItems($h),
                $h('div', [
                    $this->githubButton($h, 'mr-4'),
                    $this->controls($h),
                ], 'flex'),
            ], 'container mx-auto flex text-[#4E5973] text-sm justify-between'),
        ];
    }

    private function menuItems(callable $h): string
    {
        return $h('nav', [
            $h('ul', \array_map(
                fn(string $item) => $h('li', [$item], 'px-2 py-4'),
                $this->navigation->items,
            ),
                'flex font-semibold'),
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
            $h('span', [
                $icon('w-4 h-4'),
                $this->navigation->githubName,
            ], 'name px-2.5 py-1.5 flex gap-x-2'),
            $h('span', [$this->navigation->githubStars], 'stars px-2.5 py-1.5 inline-block'),
        ], 'github flex border border-solid border-[#E2E2E2] rounded divide-x font-[Helvetica] font-bold text-xs self-center ' . $className);
    }

    private function controls(callable $h): string
    {
        [$big, $small] = $this->navigation->controls + ['', ''];
        return $h('ul', [
            $h('li', [$big], 'px-2 py-1.5 self-center rounded bg-[#00A538] text-white '),
            $h('li', [$small], 'px-2 py-1.5 self-center'),
        ],
            'controls flex');
    }
}
