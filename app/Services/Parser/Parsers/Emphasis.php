<?php
namespace Coyote\Services\Parser\Parsers;

use Collective\Html\HtmlBuilder;
use Coyote\Repositories\Eloquent\UserRepository;

class Emphasis implements Parser
{
    public function __construct(
        private int            $userId,
        private UserRepository $user)
    {
    }

    public function parse(string $text): string
    {
        if (str_starts_with($text, '!')) {
            $user = $this->user->find($this->userId, ['id']);
            if ($user && $user->can('forum-emphasis')) {
                return str_starts_with($text, '!!')
                    ? $this->emphasis($text, ['style' => 'color: #b60016'])
                    : $this->emphasis($text);
            }
        }
        return $text;
    }

    private function emphasis(string $text, array $attributes = []): string
    {
        $builder = $this->htmlBuilder();
        return $builder->tag('strong', \lTrim($text, '!'), $attributes);
    }

    private function htmlBuilder(): HtmlBuilder
    {
        return app(HtmlBuilder::class);
    }
}
