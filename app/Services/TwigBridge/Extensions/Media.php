<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Http\Factories\MediaFactory;
use Coyote\Services\Media\MediaInterface;
use Coyote\Services\Thumbnail\Objects\Microblog;
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
            new Twig_SimpleFunction('logo', [$this, 'logo']),
            new Twig_SimpleFunction('thumbnail', [$this, 'thumbnail'])
        ];
    }

    /**
     * @param string $filename
     * @return string
     * @throws \Exception
     */
    public function userPhoto($filename)
    {
        if (!$filename) {
            return cdn('img/avatar.png');
        }

        if (is_string($filename)) {
            return $this->getMediaUrl('photo', $filename)->url();
        } elseif ($filename instanceof MediaInterface) {
            return $filename->url();
        } else {
            throw new \Exception('Parameter needs to be either string or MediaInterface object.');
        }
    }

    /**
     * @param string $filename
     * @return string
     */
    public function logo($filename)
    {
        return $filename ? (string) $this->getMediaUrl('logo', $filename)->url() : cdn('img/logo-gray.png');
    }

    /**
     * Generate thumbnail URL for microblog attachments...
     *
     * @param MediaInterface $media
     * @return string
     */
    public function thumbnail(MediaInterface $media)
    {
        return $media->url()->thumbnail(new Microblog());
    }

    /**
     * @param string $factory
     * @param string $filename
     * @return MediaInterface
     */
    private function getMediaUrl($factory, $filename)
    {
        return $this->getMediaFactory()->make($factory, ['file_name' => $filename]);
    }
}
