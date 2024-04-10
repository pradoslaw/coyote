<?php
namespace Neon\View\Components\Event;

use Neon\View\Html\Render;
use Neon\View\Html\Tag;

readonly class EventHtml
{
    public function __construct(private \Neon\View\Components\Event\Event $event)
    {
    }

    public function render(Render $h): Tag
    {
        $border = 'border-solid border-l-4 border-[#00A538]';
        return $h->tag('div', ['class' => "event bg-white rounded-lg p-4 mb-4 flex  $border"], [
            $h->tag('div', ['class' => 'date self-center ml-1 mr-6'], [
                $h->tag('span', ['class' => 'font-bold'], [$this->event->date]),
                $h->tag('span', ['class' => 'mx-2'], ['|']),
                $h->tag('span', ['class' => 'font-bold'], [$this->event->dayShortName]),
            ]),
            $h->tag('div', ['class' => 'self-center '], [
                $h->tag('h2', ['class' => 'font-medium text-base mb-1'], [
                    $h->tag('a', ['href' => $this->event->titleUrl, 'class' => 'hover:text-[#00A538]'], [
                        $this->event->title,
                    ]),
                ]),
                $h->tag('ul', [], \array_map(
                    fn($tag) => $h->tag('li',
                        ['class' => 'inline-block mr-2 py-px px-1.5 text-xs leading-5 text-[#22488C] bg-[#E3E8F1] rounded-md font-[Arial]'],
                        [$tag]),
                    $this->event->tags)),
            ]),
            $h->tag('div', ['class' => 'details flex text-center ml-auto'], [
                $h->tag('span', ['class' => 'text-[#4E5973] self-center text-sm mx-2'], [$this->event->city]),
                $h->tag('span', ['class' => 'text-[#4E5973] self-center text-sm mx-2'], [$this->event->kind]),
                $h->tag('span', ['class' => 'text-[#4E5973] self-center text-sm mx-2'], [$this->event->pricing]),
            ]),
        ]);
    }
}
