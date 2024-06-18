<?php
namespace Coyote\Domain\Administrator;

use Coyote\Services\Media;

class AvatarCdn
{
    public function avatar(?string $filename): string
    {
        if ($filename) {
            $image = $this->image($filename);
            if ($image->getFilename()) {
                return $image->url();
            }
        }
        return cdn('/img/avatar.png');
    }

    private function image(string $filename): Media\File
    {
        /** @var Media\Factory $factory */
        $factory = app(Media\Factory::class);
        return $factory->make('photo', ['file_name' => $filename]);
    }
}
