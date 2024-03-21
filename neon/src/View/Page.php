<?php
namespace Neon\View;

use Neon\View\Head\Head;
use Neon\View\Head\Script;
use Neon\View\Head\Style;

readonly class Page
{
    /** @var Head[] */
    private array $heads;

    public function __construct(array $head, private array $body)
    {
        $this->heads = [
            ...$head,
            new Script('https://cdn.tailwindcss.com'),
            new Style('fonts/switzer/switzer.css'),
        ];
    }

    public function html(callable $h): string
    {
        return '<!DOCTYPE html>' .
            $h('html', [
                $h('head', [
                    '<meta charset="utf-8">',
                    ...\array_map(
                        fn(Head $head) => $head->headHtml($h),
                        $this->heads),
                ]),
                $h('body',
                    \array_merge(...\array_map(
                        fn(Item $item) => $item->html($h),
                        $this->body)),
                    'bg-[#F0F2F5] font-[Switzer]'),
            ]);
    }
}
