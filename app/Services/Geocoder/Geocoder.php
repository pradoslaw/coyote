<?php

namespace Coyote\Services\Geocoder;

use GuzzleHttp\Client as HttpClient;

class Geocoder implements GeocoderInterface
{
    const GEOCODE_URL = 'https://maps.googleapis.com';

    /**
     * @var string
     */
    private $appKey;

    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * @param HttpClient $client
     * @param $appKey
     */
    public function __construct(HttpClient $client, $appKey)
    {
        $this->client = $client;
        $this->appKey = $appKey;
    }

    /**
     * @inheritdoc
     */
    public function geocode(string $address)
    {
        $result = $this->request(['address' => $address]);

        if ($result['status'] === 'OK') {
            $latlng = $result['results'][0]['geometry']['location'];
            return new Location(['latitude' => $latlng['lat'], 'longitude' => $latlng['lng']]);
        }

        return new Location();
    }

    /**
     * Make HTTP request
     *
     * @param array $query
     * @return array|bool|float|int|string
     */
    protected function request(array $query)
    {
        $response = $this->client->request('GET', '/maps/api/geocode/json', [
            'base_uri'      => self::GEOCODE_URL,
            'http_errors'   => true,
            'query'         => array_merge($query, ['key' => $this->appKey])
        ]);

        return json_decode((string) $response->getBody(), true);
    }
}
