<?php

namespace Coyote\Services\GeoIp;

use GuzzleHttp\Client as HttpClient;

class GeoIp
{
    const VERSION = '1.0';

    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * GeoIp constructor.
     *
     * @param HttpClient $client
     * @param $host
     * @param $port
     */
    public function __construct(HttpClient $client, $host, $port)
    {
        $this->client = $client;
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Geocode IP address and return location array
     *
     * @param $ip
     * @return mixed
     */
    public function ip($ip)
    {
        if (!$this->isValidIp($ip)) {
            return false;
        }

        return $this->request('ip/' . $ip);
    }

    /**
     * Geocode city and return coordinates and alternative names
     *
     * @param $city
     * @return mixed
     */
    public function city($city)
    {
        return $this->request('city/' . urlencode($city));
    }

    /**
     * Make HTTP request
     *
     * @param $path
     * @return array|bool|float|int|string
     */
    protected function request($path)
    {
        $response = $this->client->request('GET', $path, ['base_uri' => $this->getBaseUrl(), 'http_errors' => true]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * @return string
     */
    protected function getBaseUrl()
    {
        return 'http://' . $this->host .
            ($this->port != '' && $this->port != 80 ? (':' . $this->port) : '') .
                '/' . self::VERSION . '/';
    }

    /**
     * @param string $ip
     * @return bool
     */
    protected function isValidIp($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
}
