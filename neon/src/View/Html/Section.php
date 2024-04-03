<?php
namespace Neon\View\Html;

readonly class Section implements Item
{
    public function __construct(
        private string $breadcrumbRoot,
        private string $breadcrumbItem,
        private string $sectionTitle,
        private string $subsectionTitle,
        private array  $children,
    )
    {
    }

    public function html(Render $h): array
    {
        return [
            $h->tag('div', [
                $h->tag('nav', [
                    $h->tag('ul', [
                        $h->tag('li', [$this->breadcrumbRoot], 'inline'),
                        $h->tag('span', ['/'], 'mx-1 text-[#00A538]'),
                        $h->tag('li', [$this->breadcrumbItem], 'inline'),
                    ], 'text-xs font-[Arial]'),
                ],[]),
                $this->topRightHighlight($h),
                $h->tag('h1', [$this->sectionTitle], 'font-semibold text-2xl'),
            ], 'bg-white rounded-lg py-[26px] p-4 mb-8'),

            $h->tag('h2',
                [$this->subsectionTitle],
                'text-xs text-[#053B00] mb-4 tracking-tight'),

            ...\array_map(
                fn(Event $event) => $event->html($h),
                $this->children),
        ];
    }

    private function topRightHighlight(Render $h): Tag
    {
        return $h->tag('div', [], [
            'style'       => 'width:580px; height:580px; border-radius:580px; background:rgba(0, 165, 56, 0.60); filter:blur(50px); position:absolute; right:-290px; bottom:50%;',
            'parentClass' => 'relative overflow-hidden',
        ]);
    }
}
