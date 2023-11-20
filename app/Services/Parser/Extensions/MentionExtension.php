<?php
namespace Coyote\Services\Parser\Extensions;

use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Services\Parser\MentionGenerator;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\Mention\MentionParser;

class MentionExtension implements ExtensionInterface
{
    private MentionGenerator $generator;

    public function __construct(private UserRepository $user)
    {
        $this->generator = new MentionGenerator($this->user);
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $this->addParser($environment, 'basic', '[a-zA-Z0-9ąćęłńóśźżĄĆĘŁŃÓŚŹŻ#_@\-]+');
        $this->addParser($environment, 'extended', '{[a-zA-Z0-9ąćęłńóśźżĄĆĘŁŃÓŚŹŻ#_@\-. \(\)]+}');
    }

    private function addParser(EnvironmentBuilderInterface $environment, string $name, string $pattern): void
    {
        $environment->addInlineParser(new MentionParser($name, '@', $pattern, $this->generator));
    }
}
