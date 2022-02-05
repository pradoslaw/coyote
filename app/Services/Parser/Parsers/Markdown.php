<?php

namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Parser\MentionGenerator;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Mention\MentionExtension;
use League\CommonMark\MarkdownConverter;

class Markdown implements ParserInterface
{
    public function __construct(private UserRepository $user)
    {
    }

    public function parse(string $text): string
    {
        $environment = new Environment($this->defaultConfig());
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new MentionExtension());

        $converter = new MarkdownConverter($environment);

        return (string) $converter->convert($text);
    }

    protected function defaultConfig(): array
    {
        return [
            'renderer' => [
                'soft_break'      => "<br>\n",
            ],
            'mentions' => [
                'basic' => [
                    'prefix'    => '@',
                    'pattern'   => '[a-zA-Z0-9ąćęłńóśźżĄĆĘŁŃÓŚŹŻ#_@\-]+',
                    'generator' => new MentionGenerator($this->user)
                ],
                'extended' => [
                    'prefix'    => '@',
                    'pattern'   => '{[a-zA-Z0-9ąćęłńóśźżĄĆĘŁŃÓŚŹŻ#_@\-. \(\)]+}',
                    'generator' => new MentionGenerator($this->user)
                ]
            ]
        ];
    }
}
