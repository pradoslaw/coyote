<?php
namespace Neon\View\Components;

use Neon\View\Html\Item;
use Neon\View\Html\Render;
use Neon\View\Language\Language;
use Neon\View\Theme;

readonly class AddEventHtml implements Item
{
    public function __construct(
        private Language $lang,
        private string   $learnMoreUrl,
        private Theme    $theme,
    )
    {
    }

    public function render(Render $h): array
    {
        return [
            $h->tag('div', ['class' => "{$this->theme->addEventBackground} p-4 rounded-lg"], [
                $h->tag('h2',
                    ['class' => "font-medium text-base {$this->theme->addEventHeadingColor} mb-4 tracking-tight"],
                    [$this->lang->t('Are you hosting an event?')]),
                $h->tag('p', ['class' => "mb-4 font-[Inter] text-xs {$this->theme->addEventParagraphColor}"], [
                    $this->lang->t("Add your event to our calendar and we'll gladly provide media support."),
                ]),
                $h->tag('a', [
                    'href'  => $this->learnMoreUrl,
                    'class' => "text-sm {$this->theme->addEventLinkColor}",
                ],
                    [$this->lang->t('Learn more')]),
            ]),
        ];
    }
}
