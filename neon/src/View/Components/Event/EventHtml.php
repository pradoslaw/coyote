<?php
namespace Neon\View\Components\Event;

use Neon\View\Html\Render;
use Neon\View\Html\Tag;
use Neon\View\Theme;

readonly class EventHtml
{
    public function __construct(
        private Event  $event,
        private string $csrf,
        private Theme  $theme,
    )
    {
    }

    public function render(Render $h): Tag
    {
        return $h->tag('div', ['class' => "event {$this->theme->eventStyle} rounded-lg p-4 mb-4 flex {$this->theme->eventBorder}"], [
            $h->tag('div', ['class' => 'date self-center ml-1 mr-6'], [
                $h->tag('span', ['class' => 'font-bold'], [$this->event->date]),
                $h->tag('span', ['class' => 'mx-2'], ['|']),
                $h->tag('span', ['class' => 'font-bold'], [$this->event->dayShortName]),
            ]),
            $h->tag('div', ['class' => 'self-center '], [
                $h->tag('h2', ['class' => 'font-medium text-base mb-1'], [
                    $h->tag('a', ['href' => $this->event->titleUrl, 'class' => 'hover:text-[#00A538]', 'id' => $this->event->key], [
                        $this->event->title,
                    ]),
                    $h->html(<<<script
                        <script>
                            (function () {
                              const link = document.getElementById('{$this->event->key}');
                              link.addEventListener('click', () => {
                                fetch('/Settings/Ajax', {
                                  method:'POST', 
                                  headers: {
                                    'X-CSRF-TOKEN': '{$this->csrf}',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                  },
                                  body: JSON.stringify({key: 'event.click.neon.{$this->event->key}'})
                                });
                              });
                            })();
                        </script>
                        script,),
                ]),
                $h->tag('ul', [], \array_map(
                    fn($tag) => $h->tag('li',
                        ['class' => "inline-block mr-2 py-px px-1.5 text-xs leading-5 {$this->theme->eventTag} rounded-md font-[Arial]"],
                        [$tag]),
                    $this->event->tags)),
            ]),
            $h->tag('div', ['class' => "details flex text-center ml-auto {$this->theme->eventDetailsStyle}"], [
                $h->tag('span', ['class' => 'self-center text-sm mx-2'], [$this->event->city]),
                $h->tag('span', ['class' => 'self-center text-sm mx-2'], [$this->event->kind]),
                $h->tag('span', ['class' => 'self-center text-sm mx-2'], [$this->event->pricing]),
            ]),
        ]);
    }
}
