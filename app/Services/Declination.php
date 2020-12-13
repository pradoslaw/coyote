<?php

namespace Coyote\Services;

/**
 * Klasa zawiera metode zwracajaca poprawna forme jezykowa (deklinacja)
 * @example
 * echo Declination::format(10, array('sekunda', 'sekundy', 'sekund'));
 */
class Declination
{
    /**
     * @param int $value
     * @param array $declination
     * @param bool|false $absolute True zwraca jedynie sam tekst bez liczebnika
     * @return string
     */
    public static function format($value, $declination, $absolute = false)
    {
        if ($value == 1) {
            return self::abs($value, $declination[0], $absolute);
        } else {
            $unit = $value % 10;
            $decimal = round(($value % 100) / 10);

            if (($unit == 2 || $unit == 3 || $unit == 4) && ($decimal != 1)) {
                return self::abs($value, $declination[1], $absolute);
            } else {
                return self::abs($value, $declination[2], $absolute);
            }
        }
    }

    /**
     * @param int $value
     * @param string $declination
     * @param bool $flag
     * @return string
     */
    private static function abs($value, $declination, $flag)
    {
        if ($flag) {
            return $declination;
        }

        return $value . ' ' . $declination;
    }
}
