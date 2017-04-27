<?php

namespace Coyote\Services\Cardinity;

class ResultMapper
{
    /**
     * @var mixed
     */
    private $result;

    /**
     * @param mixed $result
     */
    public function __construct($result)
    {
        $this->result = $result;
    }

    /**
     * @param array $response
     * @return mixed
     */
    public function map(array $response)
    {
        foreach ($response as $key => $value) {
            if ($key === 'authorization_information') {
                $this->result->{'authorizationInformation'} = $this->transformAuthorizationInformation($value);
            } elseif (property_exists($this->result, camel_case($key))) {
                $this->result->{camel_case($key)} = $value;
            }
        }

        return $this->result;
    }

    /**
     * Transform AuthorizationInformation result array to object
     * @param array $data
     * @return AuthorizationInformation
     */
    private function transformAuthorizationInformation($data)
    {
        return (new static(new AuthorizationInformation()))->map($data);
    }
}
