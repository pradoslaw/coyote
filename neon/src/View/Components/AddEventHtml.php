<?php
namespace Neon\View\Components;

use Neon\View\Html\Item;
use Neon\View\Html\Render;
use Neon\View\Language\Language;

readonly class AddEventHtml implements Item
{
    public function __construct(
        private Language $lang,
        private string   $learnMoreUrl,
    )
    {
    }

    public function render(Render $h): array
    {
        return [
            $h->tag('div', ['class' => 'bg-white p-4 rounded-lg'], [
                $h->tag('h2',
                    ['class' => 'font-medium text-base text-[#053B00] mb-4 tracking-tight  text-[#070707]'],
                    [$this->lang->t('Are you hosting an event?')]),
                $h->tag('p', ['class' => 'mb-4 font-[Inter] text-xs text-[#737578]'], [
                    $this->lang->t("Add your event to our calendar and we'll gladly provide media support."),
                ]),
                $h->tag('a', [
                    'href'  => $this->learnMoreUrl,
                    'class' => 'text-sm text-[#00A538]',
                ],
                    [$this->lang->t('Learn more')]),
            ]),
        ];
    }
}
