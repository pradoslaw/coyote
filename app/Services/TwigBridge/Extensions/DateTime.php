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
            new Twig_SimpleFunction('diff_in_days', [&$this, 'diffInDays'])
        ];
    }

    /**
     * @param $dateTime
     * @param null|mixed $now
     * @return int
     */
    public function diffInMonths($dateTime, $now = null)
    {
        $dateTime = carbon($dateTime);

        return carbon($now)->diffInMonths($dateTime);
    }

    /**
     * @param $dateTime
     * @param null|mixed $now
     * @return int
     */
    public function diffInDays($dateTime, $now = null)
    {
        $dateTime = carbon($dateTime);

        return carbon($now)->diffInDays($dateTime);
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
                return format_date($dateTime, $diffForHumans);
            }),

            new Twig_SimpleFilter('timestamp', function ($dateTime) {
                return carbon($dateTime)->getTimestamp();
            }),

            new Twig_SimpleFilter('iso_8601', function ($dateTime) {
                return carbon($dateTime)->format(Carbon::ISO8601);
            }),

            new Twig_SimpleFilter('diff_for_humans', function ($dateTime) {
                return carbon($dateTime)->diffForHumans();
            }),

            new Twig_SimpleFilter('date_localized', function ($dateTime, $format) {
                return carbon($dateTime)->formatLocalized($format);
            })
        ];
    }
}
