<?php

namespace Coyote\GeoIp\Normalizers;

class Locale implements NormalizerInterface
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * Locale constructor.
     * @param string $locale
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Transform result set array into associative array. This method tries to fix city name
     * by searching for local name
     *
     * @param array $data
     * @return mixed
     */
    public function normalize(array $data)
    {
        // we just want a first hit
        $result = array_first($data);

        if (!empty($result['alternatives'])) {
            foreach ($result['alternatives'] as $alternative) {
                if ($alternative['language'] === $this->locale) {
                    $result['name'] = $alternative['name'];

                    break;
                }
            }
        }

        return $result;
    }
}
