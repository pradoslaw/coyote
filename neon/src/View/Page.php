<?php
namespace Neon\View;

readonly class Page
{
    public function __construct(private string $title, private array $sections)
    {
    }

    public function html(callable $h): string
    {
        return '<!DOCTYPE html>' .
            $h('html', [
                $h('head', [
                    '<meta charset="utf-8">',
                    $h('title', [$this->title]),
                    '<script src="https://cdn.tailwindcss.com"></script>',
                    '<link rel="stylesheet" type="text/css" href="fonts/switzer/switzer.css"/>',
                ]),
                $h('body',
                    \array_merge(...\array_map(
                        fn(Item $item) => $item->html($h),
                        $this->sections)),
                    'bg-[#F0F2F5] font-[Switzer]'),
            ]);
    }
}
