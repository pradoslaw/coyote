<?php

namespace Coyote\GeoIp\Normalizers;

interface NormalizerInterface
{
    /**
     * @param array $data
     * @return mixed
     */
    public function normalize(array $data);
}
