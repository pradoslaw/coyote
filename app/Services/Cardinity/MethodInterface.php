<?php

namespace Coyote\Services\Cardinity;

interface MethodInterface
{
    /**
     * HTTP method POST
     */
    const POST = 'POST';

    /**
     * HTTP method PATCH
     */
    const PATCH = 'PATCH';

    /**
     * HTTP method GET
     */
    const GET = 'GET';

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return mixed
     */
    public function getResult();

    /**
     * @return array
     */
    public function getAttributes(): array;
}
