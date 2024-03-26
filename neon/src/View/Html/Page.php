<?php
namespace Neon\View\Html;

use Neon\View\Html\Head\Head;
use Neon\View\Html\Head\Style;

readonly class Page
{
    /** @var Head[] */
    private array $heads;

    public function __construct(array $head, private array $body)
    {
        $this->heads = [
            ...$head,
            // new Script('https://cdn.tailwindcss.com'), // for debug
            new Style('css/neon.css'),
            new Style('fonts/switzer/switzer.css'),
        ];
    }

    public function html(callable $h): string
    {
        return '<!DOCTYPE html>' .
            $h('html', [
                $h('head', [
                    $h('meta', [], ['charset' => 'utf-8']),
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
