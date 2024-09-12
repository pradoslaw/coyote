<?php
namespace Coyote\Domain\Administrator;

use Coyote\Services\Media;

class AvatarCdn
{
    public function avatar(?string $filename): ?Media\File
    {
        /** @var Media\Factory $factory */
        $factory = app(Media\Factory::class);
        return $factory->make('photo', ['file_name' => $filename]);
    }
}
