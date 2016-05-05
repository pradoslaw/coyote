<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Http\Factories\MediaFactory;
use Twig_Extension;
use Twig_SimpleFunction;

class Media extends Twig_Extension
{
    use MediaFactory;

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'TwigBridge_Extension_Media';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            // funkcja generuje URL do zdjecia usera lub domyslny avatar jezeli brak
            new Twig_SimpleFunction('user_photo', [$this, 'userPhoto']),
            new Twig_SimpleFunction('logo', [$this, 'logo'])
        ];
    }

    /**
     * @param string $filename
     * @return string
     */
    public function userPhoto($filename)
    {
        return $filename ? $this->getMediaUrl('user_photo', $filename) : cdn('img/avatar.png');
    }

    /**
     * @param string $filename
     * @return string
     */
    public function logo($filename)
    {
        return $filename ? $this->getMediaUrl('logo', $filename) : cdn('img/logo-gray.png');
    }

    /**
     * @param string $factory
     * @param string $filename
     * @return string
     */
    private function getMediaUrl($factory, $filename)
    {
        return $this->getMediaFactory($factory)->make(['file_name' => $filename])->url();
    }
}
