<?php
namespace Coyote\Domain\Online;

readonly class ViewerUser
{
    public function __construct(
        public string  $name,
        public ?string $groupName,
        public ?string $groupShortName,
        public ?string $avatarUrl,
        public string  $profileUrl,
        public int     $vCardId,
    ) {}
}
