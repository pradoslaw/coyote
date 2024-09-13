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
            new TwigFunction('user_photo', $this->userPhoto(...)),
            new TwigFunction('logo', $this->logo(...)),
        ];
    }

    public function userPhoto($filename): string
    {
        return $this->getMediaUrl('photo', $filename, 'img/avatar.png');
    }

    public function logo($filename, $secure = null): string
    {
        return $this->getMediaUrl('logo', $filename, 'img/logo-gray.png', $secure);
    }

    private function getMediaUrl($factory, $filename, $placeholder, $secure = null): string
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
