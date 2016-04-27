<?php

namespace Coyote\Http;

use Illuminate\Http\Request;

class CustomRequest extends Request
{
    public function ip()
    {
        $ip = $this->getClientIp();

        if ('production' == env('APP_ENV')) {
            /*
             * Jezeli obecny jest ten naglowek, to oznacza, ze wlaczony jest CloudFlare. Nalezy wydobyc oryginalne IP
             * uzytkownika
             */
            if ($this->server('HTTP_CF_CONNECTING_IP') && $this->server('HTTP_X_FORWARDED_FOR')) {
                // jezeli adresow jest wiecej niz 1 (np. user laczy sie dodatkowo z proxy), to pierwszy bedzie ten wlasciwy
                $xForwardedFor = array_reverse(explode(',', $this->server('HTTP_X_FORWARDED_FOR')));

                if (filter_var(
                    $xForwardedFor[0],
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                ) !== false) {
                    foreach (config('cloudflare.ip') as $cidr) {
                        list($subnet, $mask) = explode('/', $cidr);
                        if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet)) {
                            $ip = $xForwardedFor[0]; // przypisanie prawidlowego IP usera
                            break;
                        }
                    }
                }
            }
        }

        return $ip;
    }

    /**
     * @return mixed
     */
    public function browser()
    {
        return filter_var($this->header('User-Agent'), FILTER_SANITIZE_STRING);
    }
}
