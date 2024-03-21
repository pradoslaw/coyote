<?php
namespace Neon\View;

readonly class Section implements Item
{
    public function __construct(
        private string $rootBreadcrumb,
        private string $sectionTitle,
        private array  $children,
    )
    {
    }

    public function html(callable $h): array
    {
        return [
            $h('div', [
                $h('div', [
                    $h('nav', [
                        $h('ul', [
                            $h('li', [$this->rootBreadcrumb], 'inline'),
                            $h('span', ['/'], 'mx-1 text-[#00A538]'),
                            $h('li', ['Events'], 'inline'),
                        ], 'text-xs font-[Arial]'),
                    ]),
                    $h('h1', [$this->sectionTitle], 'font-semibold text-2xl'),
                ], 'bg-white rounded-lg py-5 p-4 mb-8'),
                $h('div',
                    ['Events with our patronage'],
                    'text-xs text-[#053B00] mb-4 tracking-tight'),
                ...\array_map(
                    fn(Event $event) => $event->html($h),
                    $this->children),
            ], 'container mx-auto my-4'),
        ];
    }
}
