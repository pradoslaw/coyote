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
     * Generate thumbnail URL for microblog attachments...
     *
     * @param MediaInterface $media
     * @return string
     */
    public function thumbnail(MediaInterface $media)
    {
        // @todo obecnie generuje miniatury tylko dla mikroblogow. moze warto to rozszerzyc?
        return $media->getFactory()->getThumbnail()->url(new Microblog())->make($media->url());
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
