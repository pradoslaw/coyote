<?php declare(strict_types=1);

namespace Coyote\Services\Elasticsearch;

use GuzzleHttp\Client;

class Api
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string|null
     */
    private $jwtToken;

    public function __construct(Client $client, string $host, int $port)
    {
        $this->client = $client;
        $this->host = $host;
        $this->port = $port;
    }

    public function setJwtToken(string $jwtToken)
    {
        $this->jwtToken = $jwtToken;
    }

    public function search(string $query, string $model = null)
    {
        return $this->get('/search', ['q' => $query, 'model' => $model]);
    }

    protected function get(string $path, array $params = []): Hits
    {
        $response = $this->client->get($path, [
            'base_uri'  => sprintf('http://%s:%d', $this->host, $this->port),
            'query'     => $params
        ]);

        $body = json_decode((string) $response->getBody(), true);

        return new Hits($body['hits'], $body['took'], $body['total']);
    }
}
