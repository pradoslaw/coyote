<?php

namespace Coyote\Services\Cardinity;

use Coyote\Services\Cardinity\Payment\Create;
use Coyote\Services\Cardinity\Payment\Finalize;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class Client
{
    const URI = 'https://api.cardinity.com/v1/';

    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @return Client
     */
    public static function create()
    {
        return new self(config('services.cardinity.key'), config('services.cardinity.secret'));
    }

    /**
     * @param string $key
     * @param string $secret
     */
    public function __construct(string $key, string $secret)
    {
        $stack = HandlerStack::create();

        $middleware = new Oauth1([
            'consumer_key'   => $key,
            'consumer_secret'=> $secret,
            'token_secret'   => ''
        ]);
        $stack->push($middleware);

        $this->client = new HttpClient([
            'base_uri'      => self::URI,
            'handler'       => $stack,
            'auth'          => 'oauth'
        ]);
    }

    /**
     * @param MethodInterface $method
     * @return mixed
     */
    public function call(MethodInterface $method)
    {
        try {
            $response = $this->client->request($method->getMethod(), $method->getPath(), $this->getOptions($method));

            $result = $method->getResult();
            $mapper = new ResultMapper($result);

            return $mapper->map(json_decode((string) $response->getBody(), true));
        } catch (ClientException $e) {
            $exceptionMapper = new ExceptionMapper();

            throw $exceptionMapper->dispatch($e);
        } catch (\Exception $e) {
            throw new Exceptions\UnexpectedError($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param array $data
     * @return Payment
     */
    public function createPayment(array $data): Payment
    {
        return $this->call(new Create($data));
    }

    /**
     * @param string $paymentId
     * @param string $authorizeData
     * @return Payment
     */
    public function finalizePayment(string $paymentId, string $authorizeData): Payment
    {
        return $this->call(new Finalize($paymentId, $authorizeData));
    }

    /**
     * Prepare request options for particular method
     *
     * @param MethodInterface $method
     * @return array
     */
    private function getOptions(MethodInterface $method)
    {
        if ($method->getMethod() === MethodInterface::GET) {
            return [
                'query' => $method->getAttributes(),
            ];
        }

        return [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($this->formatAttributes($method->getAttributes()), JSON_FORCE_OBJECT)
        ];
    }

    /**
     * Prepare request attributes
     *
     * @param array $data
     * @return array
     */
    private function formatAttributes(array $data)
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $data[$key] = $this->formatAttributes($value);
                continue;
            }

            if (is_float($value)) {
                $value = number_format($value, 2, '.', '');
            }

            if ($key === 'pan') {
                $value = str_replace('-', '', $value);
            }
        }

        return $data;
    }
}
