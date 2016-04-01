<?php

namespace TwigBridge\Extensions;

use Carbon\Carbon;
use Twig_Extension;
use Twig_SimpleFunction;
use Twig_SimpleFilter;

class DateTime extends Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'TwigBridge_Extension_DateTime';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            /**
             * Diff in months helper. We use it to calculate the age of the topic.
             */
            new Twig_SimpleFunction(
                'diff_in_months',
                function ($dateTime, $now = null) {
                    $dateTime = $this->toCarbon($dateTime);

                    if (!$now) {
                        $now = Carbon::now();
                    } else {
                        $now = $this->toCarbon($now);
                    }
                    return $now->diffInMonths($dateTime);
                }
            )
        ];
    }

    /**
     * Dodatkowe filtry Twig zwiazane z formatowaniem danych uzytkownika
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('format_date', function ($dateTime, $diffForHumans = true) {
                $format = auth()->check() ? auth()->user()->date_format : '%Y-%m-%d %H:%M';

                $dateTime = $this->toCarbon($dateTime);
                $now = Carbon::now();

                if (!$diffForHumans) {
                    return $dateTime->formatLocalized($format);
                } elseif ($dateTime->diffInHours($now) < 1) {
                    return $dateTime->diffForHumans(null, true) . ' temu';
                } elseif ($dateTime->isToday()) {
                    return 'dziś, ' . $dateTime->format('H:i');
                } elseif ($dateTime->isYesterday()) {
                    return 'wczoraj, ' . $dateTime->format('H:i');
                } else {
                    return $dateTime->formatLocalized($format);
                }
            }),

            new Twig_SimpleFilter('timestamp', function ($dateTime) {
                if ($dateTime instanceof Carbon) {
                    return $dateTime->getTimestamp();
                } else {
                    return strtotime($dateTime);
                }
            })
        ];
    }

    private function toCarbon($dateTime)
    {
        if (!$dateTime instanceof Carbon) {
            $dateTime = new Carbon($dateTime);
        }

        return $dateTime;
    }
}
