<?php
namespace Tests\Unit\BaseFixture\Server\Laravel\Fake;

use Override;

class GithubStars extends \Coyote\Domain\Github\GithubStars
{
    #[Override]
    public function fetchStars(): ?int
    {
        return null;
    }
}
