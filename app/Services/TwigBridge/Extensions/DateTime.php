<?php
namespace Coyote\Services\TwigBridge\Extensions;

use Twig_Extension;
use Twig_SimpleFilter;

class DateTime extends Twig_Extension
{
    public function getName(): string
    {
        return 'TwigBridge_Extension_DateTime';
    }

    public function getFilters(): array
    {
        return [
            new Twig_SimpleFilter('format_date', function ($dateTime, $diffForHumans = true) {
                return format_date($dateTime, $diffForHumans);
            }),
            new Twig_SimpleFilter('date_localized', function ($dateTime, $format) {
                return carbon($dateTime)->formatLocalized($format);
            }),
        ];
    }
}
