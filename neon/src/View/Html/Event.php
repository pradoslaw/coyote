<?php
namespace Neon\View\Html;

use Neon\View\ViewModel;

readonly class Event
{
    public function __construct(private ViewModel\Event $event)
    {
    }

    public function html(Render $h): string
    {
        $border = 'border-solid border-l-4 border-[#00A538]';
        return $h->tag('div', 'event bg-white rounded-lg p-4 mb-4 flex justify-between ' . $border, [
            $h->tag('div', 'date self-center w-2/12 ml-1', [
                $h->tag('span', 'font-bold', [$this->event->date]),
                $h->tag('span', 'mx-2', ['|']),
                $h->tag('span', 'font-bold', [$this->event->dayShortName]),
            ]),
            $h->tag('div', 'self-center w-1/2', [
                $h->tag('h2', 'font-medium text-base mb-1', [$this->event->title]),
                $h->tag('ul', [], \array_map(
                    fn($tag) => $h->tag('li', 'inline-block mr-2 py-px px-1.5 text-xs leading-5 text-[#22488C] bg-[#E3E8F1] rounded-md font-[Arial]', [$tag]),
                    $this->event->tags)),
            ]),
            $h->tag('div', 'details w-5/12 flex text-center', [
                $h->tag('span', 'text-[#4E5973] self-center text-sm w-1/3', [$this->event->city]),
                $h->tag('span', 'text-[#4E5973]  self-center text-sm w-1/3', [$this->event->kind]),
                $h->tag('span', 'text-[#4E5973]  self-center text-sm w-1/3', [$this->event->pricing]),
            ]),
        ]);
    }
}
