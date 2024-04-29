<?php
namespace Neon\View;

use Neon\View\Html\Head\Head;
use Neon\View\Html\Head\Style;
use Neon\View\Html\Item;
use Neon\View\Html\Render;

readonly class HtmlView
{
    private Theme $theme;

    public function __construct(
        private array $head,
        private array $body,
        bool          $darkTheme,
    )
    {
        $this->theme = new Theme($darkTheme);
    }

    public function html(): string
    {
        $h = new Render();
        $heads = [
            ...$this->head,
            // new Script('https://cdn.tailwindcss.com'), // for debug
            new Style('css/neon.css?v10'),
            new Style('fonts/switzer/switzer.css'),
            new Style('fonts/inter/inter.css'),
        ];
        $page = $h->many([
            $h->html('<!DOCTYPE html>'),
            $h->tag('html', [], [
                $h->tag('head', [], [
                    $h->tag('meta', ['charset' => 'utf-8'], []),
                    ...\array_map(fn(Head $head) => $head->render($h), $heads),
                ]),
                $h->tag('body',
                    ['class' => "font-[Switzer] px-2 lg:px-4 {$this->theme->bodyBackground}"],
                    \array_merge(...\array_map(
                        fn(Item $item) => $item->render($h),
                        $this->body))),
            ]),
        ]);
        return $page->html();
    }
}
