<?php

namespace Coyote\Http\Validators;

class HostValidator
{
    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $hosts
     * @return bool
     */
    public function validateHost($attribute, $value, $hosts)
    {
        if (empty($hosts)) {
            return true;
        }

        $urlHost = parse_url($value, PHP_URL_HOST);
        $urlHost = explode('.', $urlHost);

        if ($urlHost[0] === 'www') {
            array_shift($urlHost);
        }

        return in_array(implode('.', $urlHost), $hosts);
    }
}
