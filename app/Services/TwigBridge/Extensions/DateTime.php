<?php
namespace Coyote\Services\TwigBridge\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateTime extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_date', function ($dateTime, $diffForHumans = true) {
                return format_date($dateTime, $diffForHumans);
            }),
        ];
    }
}
