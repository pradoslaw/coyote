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
        return (string) $this->getMediaUrl('photo', $filename, 'img/avatar.png');
    }

    /**
     * @param string $filename
     * @return string
     */
    public function logo($filename)
    {
        return (string) $this->getMediaUrl('logo', $filename, 'img/logo-gray.png');
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
     * @param string $placeholder
     * @return string
     * @throws \Exception
     */
    private function getMediaUrl($factory, $filename, $placeholder)
    {
        if (!$filename) {
            return cdn($placeholder);
        }

        if (is_string($filename)) {
            return $this->getMediaFactory()->make($factory, ['file_name' => $filename])->url();
        } elseif ($filename instanceof MediaInterface) {
            return $filename->getFilename() ? $filename->url() : cdn($placeholder);
        } else {
            throw new \Exception('Parameter needs to be either string or MediaInterface object.');
        }
    }
}
