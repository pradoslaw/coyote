<?php

namespace Coyote;

use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Exception\ClientErrorResponseException;

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

        $this->client->setBaseUrl($this->getBaseUrl());
    }

    /**
     * Geocode IP address and return location array
     *
     * @param $ip
     * @return mixed
     */
    public function ip($ip)
    {
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
        try {
            $request = $this->client->get($path);
            $response = $request->send();

            return $response->json();
        } catch (ClientErrorResponseException $e) {
            $statusCode = $e->getResponse()->getStatusCode();

            if (method_exists($this, "handle${statusCode}Error")) {
                $this->{"handle${statusCode}Error"}($e);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param ClientErrorResponseException $e
     */
    protected function handle404Error(ClientErrorResponseException $e)
    {
        $message = $e->getMessage();
        $response = $e->getResponse()->json();

        if (!empty($response['error']['message'])) {
            $message = $response['error']['message'];
        }
        throw new ClientErrorResponseException($message);
    }

    /**
     * @return string
     */
    protected function getBaseUrl()
    {
        return 'http://' . $this->host . ($this->port !== '' && $this->port != 80 ? (':' . $this->port) : '') . '/' . self::VERSION . '/';
    }
}