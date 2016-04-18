<?php

namespace Coyote\Services\GeoIp\Normalizers;

interface NormalizerInterface
{
    /**
     * @param array $data
     * @return mixed
     */
    public function normalize(array $data);
}
