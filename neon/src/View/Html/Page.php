<?php
namespace Neon\View\Html;

use Neon\View\Html\Head\Head;
use Neon\View\Html\Head\Style;

readonly class Page implements Item
{
    /** @var Head[] */
    private array $heads;

    public function __construct(array $head, private array $body)
    {
        $this->heads = [
            ...$head,
            // new Script('https://cdn.tailwindcss.com'), // for debug
            new Style('css/neon.css?v6'),
            new Style('fonts/switzer/switzer.css'),
            new Style('fonts/inter/inter.css'),
        ];
    }

    public function render(Render $h): array
    {
        return [
            $h->html('<!DOCTYPE html>'),
            $h->tag('html', [], [
                $h->tag('head', [], [
                    $h->tag('meta', ['charset' => 'utf-8'], []),
                    ...\array_map(
                        fn(Head $head) => $head->render($h),
                        $this->heads),
                ]),
                $h->tag('body',
                    ['class' => 'bg-[#F0F2F5] font-[Switzer] px-2 lg:px-4'],
                    \array_merge(...\array_map(
                        fn(Item $item) => $item->render($h),
                        $this->body))),
            ]),
        ];
    }
}
