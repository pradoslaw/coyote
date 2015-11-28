<?php

namespace Coyote\Http;

use Illuminate\Http\Request;

class CustomRequest extends Request
{
    /**
     * Oto lista adresow IP CloudFlare. Nalezy sprawdzic, czy polaczenie naprawde pochodzi z ich serwerow
     *
     * @var array
     */
    private $cloudFlareIps = [
        "204.93.240.0/24", "204.93.177.0/24", "199.27.128.0/21", "173.245.48.0/20",
        "103.21.244.0/22", "103.22.200.0/22", "103.31.4.0/22", "141.101.64.0/18",
        "108.162.192.0/18", "190.93.240.0/20","188.114.96.0/20","197.234.240.0/22","198.41.128.0/17","162.158.0.0/15"
    ];

    public function ip()
    {
        $ip = $this->getClientIp();

        if ('prod' == env('APP_ENV')) {
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
                    foreach ($this->cloudFlareIps as $cidr) {
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

    public function browser()
    {
        return filter_var($this->header('User-Agent'), FILTER_SANITIZE_STRING);
    }
}
