<?php declare(strict_types=1);

namespace Coyote\Services\Elasticsearch;

use Coyote\Exceptions\ApiRequestException;
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

    public function search(SearchOptions $options)
    {
        return $this->get('/search', $options->getParams());
    }

    protected function get(string $path, array $params = []): Hits
    {
        $response = $this->client->get($path, [
            'base_uri'  => sprintf('http://%s:%d', $this->host, $this->port),
            'query'     => $params
        ]);

        if ($response->getStatusCode() === 400) {
            throw new ApiRequestException('Wprowadzone dane nie są poprawne.');
        } elseif ($response->getStatusCode() === 500) {
            throw new ApiRequestException('Nieoczekiwany błąd serwera wyszukiwarki.');
        }

        $body = json_decode((string) $response->getBody(), true);

        return new Hits($body['hits'], $body['took'], $body['total']);
    }
}
