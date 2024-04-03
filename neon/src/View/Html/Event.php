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
        return $h('div', [
            $h('div', [
                $h('span', [$this->event->date], 'font-bold'),
                $h('span', ['|'], 'mx-2'),
                $h('span', [$this->event->dayShortName], 'font-bold'),
            ], 'date self-center w-2/12 ml-1'),
            $h('div', [
                $h('h2', [$this->event->title], 'font-medium text-base mb-1'),
                $h('ul', \array_map(
                    fn($tag) => $h('li', [$tag], 'inline-block mr-2 py-px px-1.5 text-xs leading-5 text-[#22488C] bg-[#E3E8F1] rounded-md font-[Arial]'),
                    $this->event->tags)),
            ], 'self-center w-1/2'),
            $h('div', [
                $h('span', [$this->event->city], 'text-[#4E5973] self-center text-sm w-1/3'),
                $h('span', [$this->event->kind], 'text-[#4E5973]  self-center text-sm w-1/3'),
                $h('span', [$this->event->pricing], 'text-[#4E5973]  self-center text-sm w-1/3'),
            ], 'details w-5/12 flex text-center'),
        ], 'event bg-white rounded-lg p-4 mb-4 flex justify-between ' . $border);
    }
}
