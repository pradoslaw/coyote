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
            $h('nav', [$h('ul', [
                $h('li', [$this->rootBreadcrumb]),
                $h('li', ['Events']),
            ])]),

            $h('h1', [$this->sectionTitle]),
            ...\array_map(
                fn(Event $event) => $event->html($h),
                $this->children),
        ];
    }
}
