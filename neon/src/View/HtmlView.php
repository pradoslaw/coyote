<?php
namespace Neon\View;

use Neon\View\Html\Head\Head;
use Neon\View\Html\Head\Style;
use Neon\View\Html\Item;
use Neon\View\Html\Render;
use Neon\View\Html\Render\Neon\NeonTag;
use Neon\View\Html\Render\Neon\NeonTags;
use Neon\View\Html\Render\Xenon\XenonTags;
use Xenon\State;
use Xenon\Xenon;

readonly class HtmlView
{
    public function __construct(private array $head, private array $body)
    {
    }

    public function html(): string
    {
        $h = new Render(new NeonTags());
        $heads = [
            ...$this->head,
            // new Script('https://cdn.tailwindcss.com'), // for debug
            new Style('css/neon.css?v8'),
            new Style('fonts/switzer/switzer.css'),
            new Style('fonts/inter/inter.css'),
        ];
        /** @var NeonTag $page */
        $page = $h->many([
            $h->html('<!DOCTYPE html>'),
            $h->tag('html', [], [
                $h->tag('head', [], [
                    $h->tag('meta', ['charset' => 'utf-8'], []),
                    ...\array_map(fn(Head $head) => $head->render($h), $heads),
                ]),
                $h->tag('body',
                    ['class' => 'bg-[#F0F2F5] font-[Switzer] px-2 lg:px-4'],
                    [$h->html($this->bodyHtml())]),
            ]),
        ]);
        return $page->html();
    }

    private function bodyHtml(): string
    {
        $state = new State([]);
        $h = new Render(new XenonTags($state));
        $xenon = new Xenon(
            \array_merge(...\array_map(
                fn(Item $item) => $item->render($h),
                $this->body)),
            $state);
        return $xenon->html();
    }
}
