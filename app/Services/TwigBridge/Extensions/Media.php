<?php
namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Http\Factories\MediaFactory;
use Coyote\Services\Media\File;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Media extends AbstractExtension
{
    use MediaFactory;

    public function getFunctions(): array
    {
        return [
            // funkcja generuje URL do zdjecia usera lub domyslny avatar jezeli brak
            new TwigFunction('user_photo', [$this, 'userPhoto']),
            new TwigFunction('logo', [$this, 'logo']),
        ];
    }

    /**
     * @param string $filename
     * @return string
     * @throws \Exception
     */
    public function userPhoto($filename)
    {
        return (string)$this->getMediaUrl('photo', $filename, 'img/avatar.png');
    }

    /**
     * @param string $filename
     * @param bool|null $secure
     * @return string
     * @throws \Exception
     */
    public function logo($filename, $secure = null)
    {
        return (string)$this->getMediaUrl('logo', $filename, 'img/logo-gray.png', $secure);
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
        }
        if ($filename instanceof File) {
            if ($filename->getFilename()) {
                return $filename->url($secure);
            }
            return cdn($placeholder, $secure);
        }
        throw new \Exception('Parameter needs to be either string or MediaInterface object.');
    }
}
