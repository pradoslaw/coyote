<?php
namespace Neon\View;

readonly class Page
{
    public function __construct(
        private string $applicationName,
        private string $sectionTitle,
        private array  $events,
    )
    {
    }

    public function html(callable $h): string
    {
        return '<!DOCTYPE html>' .
            $h('html', [
                $h('head', [
                    '<meta charset="utf-8">',
                    $h('title', [$this->applicationName]),
                ]),
                $h('body', [
                    $h('nav', [$h('ul', [
                        $h('li', [$this->applicationName]),
                        $h('li', ['Events']),
                    ])]),
                    $h('h1', [$this->sectionTitle]),
                    ...\array_map(
                        fn(Event $event) => $event->html($h),
                        $this->events),
                ]),
            ]);
    }
}
