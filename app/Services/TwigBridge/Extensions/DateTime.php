<?php

namespace Coyote\Services\TwigBridge\Extensions;

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
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            /**
             * Diff in months helper. We use it to calculate age of the topic.
             */
            new Twig_SimpleFunction('diff_in_months', [&$this, 'diffInMonths']),

            /**
             * Diff in days helper. We use it to calculate age of the job offer.
             */
            new Twig_SimpleFunction('is_today', [&$this, 'isToday'])
        ];
    }

    /**
     * @param $dateTime
     * @param null|mixed $now
     * @return int
     */
    public function diffInMonths($dateTime, $now = null)
    {
        $dateTime = $this->toCarbon($dateTime);

        return $this->toCarbon($now)->diffInMonths($dateTime);
    }

    /**
     * @param $dateTime
     * @return bool
     */
    public function isToday($dateTime)
    {
        return $this->toCarbon($dateTime)->isToday();
    }

    /**
     * Dodatkowe filtry Twig zwiazane z formatowaniem danych uzytkownika
     *
     * @return Twig_SimpleFilter[]
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
                return $this->toCarbon($dateTime)->getTimestamp();
            }),

            new Twig_SimpleFilter('iso_8601', function ($dateTime) {
                return $this->toCarbon($dateTime)->format(Carbon::ISO8601);
            }),

            new Twig_SimpleFilter('diff_for_humans', function ($dateTime) {
                return $this->toCarbon($dateTime)->diffForHumans();
            })
        ];
    }

    /**
     * @param $dateTime
     * @return Carbon
     */
    private function toCarbon($dateTime)
    {
        if (is_null($dateTime)) {
            $dateTime = new Carbon();
        } elseif (!$dateTime instanceof Carbon) {
            $dateTime = new Carbon($dateTime);
        }

        return $dateTime;
    }
}
