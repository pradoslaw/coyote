<?php

namespace Coyote;

use Coyote\Parser\Reference\City;

class CityValidator
{
    const REGEXP = '^([a-zA-Z\x{0080}-\x{024F}]+(?:. |-| |\'))*[a-zA-Z\x{0080}-\x{024F}]*$';

    public function validateTag($attribute, $value, $parameters, $validator)
    {
        $grabber = new City();

        foreach ($grabber->grab($value) as $city) {
            if (!preg_match('/' . self::REGEXP . '/u', trim($city))) {
                return false;
            }
        }

        return true;
    }
}
