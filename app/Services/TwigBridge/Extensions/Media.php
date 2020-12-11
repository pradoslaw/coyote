<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Http\Factories\MediaFactory;
use Coyote\Services\Media\MediaInterface;
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
     * @return Twig_SimpleFunction[]
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
     * @throws \Exception
     */
    public function userPhoto($filename)
    {
        return (string) $this->getMediaUrl('photo', $filename, 'img/avatar.png');
    }

    /**
     * @param string $filename
     * @param bool|null $secure
     * @throws \Exception
     * @return string
     */
    public function logo($filename, $secure = null)
    {
        return (string) $this->getMediaUrl('logo', $filename, 'img/logo-gray.png', $secure);
    }

    /**
     * @param string $factory
     * @param string $filename
     * @param string $placeholder
     * @param bool|null $secure
     * @return string
     * @throws \Exception
     */
    private function getMediaUrl($factory, $filename, $placeholder, $secure = null)
    {
        if (!$filename) {
            return cdn($placeholder, $secure);
        }

        if (is_string($filename)) {
            return $this->getMediaFactory()->make($factory, ['file_name' => $filename])->url($secure);
        } elseif ($filename instanceof MediaInterface) {
            return $filename->getFilename() ? $filename->url($secure) : cdn($placeholder, $secure);
        } else {
            throw new \Exception('Parameter needs to be either string or MediaInterface object.');
        }
    }
}
