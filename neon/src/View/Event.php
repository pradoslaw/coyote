<?php
namespace Neon\View;

use Neon\ViewModel;

readonly class Event
{
    public function __construct(private ViewModel\Event $event)
    {
    }

    public function html(callable $h): string
    {
        return $h('div', [
            $h('div', [
                $h('span', [$this->event->date]),
                $h('span', [$this->event->dayShortName]),
            ]),
            $h('div', [
                $h('h2', [$this->event->title]),
                $h('ul', \array_map(
                    fn($tag) => $h('li', [$tag]),
                    $this->event->tags)),
            ]),
            $h('div', [
                $h('div', [$this->event->city]),
                $h('div', [$this->event->kind]),
                $h('div', [$this->event->pricing]),
            ]),
        ], 'event');
    }
}
