<?php

namespace Declination;

/**
 * Klasa zawiera metode zwracajaca poprawna forme jezykowa (deklinacja)
 * @example
 * echo Declination::format(10, array('sekunda', 'sekundy', 'sekund'));
 */
class Declination
{
    public static function format($value, $declination)
    {
        if ($value == 1) {
            return $value . ' ' . $declination[0];
        } else {
            $unit = $value % 10;
            $decimal = round(($value % 100) / 10);

            if (($unit == 2 || $unit == 3 || $unit == 4) && ($decimal != 1)) {
                return $value . ' ' . $declination[1];
            } else {
                return $value . ' ' . $declination[2];
            }
        }
    }
}