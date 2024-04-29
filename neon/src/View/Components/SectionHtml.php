<?php
namespace Neon\View\Components;

use Neon\View\Components\Event\EventHtml;
use Neon\View\Html\Item;
use Neon\View\Html\Render;
use Neon\View\Html\Tag;
use Neon\View\Theme;

readonly class SectionHtml implements Item
{
    public function __construct(
        private string $breadcrumbRoot,
        private string $breadcrumbItem,
        private string $sectionTitle,
        private string $subsectionTitle,
        private array  $children,
        private Theme  $theme,
    )
    {
    }

    public function render(Render $h): array
    {
        return [
            $h->tag('div', ['class' => "{$this->theme->sectionBackground} rounded-lg py-[26px] p-4 mb-8"], [
                $h->tag('nav', [], [
                    $h->tag('ul', ['class' => "text-xs font-[Arial] {$this->theme->breadcrumbColor}"], [
                        $h->tag('li', ['class' => 'inline'], [$this->breadcrumbRoot]),
                        $h->tag('span', ['class' => "mx-1 {$this->theme->breadcrumbSeparator}"], ['/']),
                        $h->tag('li', ['class' => 'inline'], [$this->breadcrumbItem]),
                    ]),
                ]),
                $this->topRightHighlight($h),
                $h->tag('h1',
                    ['class' => "font-semibold text-2xl {$this->theme->sectionTitleColor}"],
                    [$this->sectionTitle]),
            ]),

            $h->tag('h2',
                ['class' => "text-xs {$this->theme->subsectionTitleColor} mb-4 tracking-tight"],
                [$this->subsectionTitle]),

            ...\array_map(
                fn(EventHtml $event) => $event->render($h),
                $this->children),
        ];
    }

    private function topRightHighlight(Render $h): Tag
    {
        return $h->tag('div', [
            'style'       => 'width:580px; height:580px; border-radius:580px; background:rgba(0, 165, 56, 0.60); filter:blur(50px); position:absolute; right:-290px; bottom:50%;',
            'parentClass' => 'relative overflow-hidden',
        ], []);
    }
}
