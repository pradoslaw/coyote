<?php

namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Collective\Html\HtmlBuilder;

class Emphasis extends HashParser implements Parser
{
    const COLOR = '#B60016';

    /**
     * @var int
     */
    protected int $userId;

    /**
     * @var string
     */
    protected string $ability;

    /**
     * @var UserRepository
     */
    protected UserRepository $user;

    /**
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    /**
     * @param string $ability
     * @return $this
     */
    public function setAbility(string $ability): static
    {
        $this->ability = $ability;

        return $this;
    }

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @param string $text
     * @return string
     */
    public function parse(string $text): string
    {
        if (!str_starts_with($text, '!')) {
            return $text;
        }

        $user = $this->user->find($this->userId, ['id']);

        if (!$user || !$user->can($this->ability)) {
            return $text;
        }

        return (str_starts_with($text, '!!')) ? $this->emphasisWithColor($text) : $this->emphasisWithBold($text);
    }

    /**
     * @param string $text
     * @return string
     */
    private function emphasisWithBold(string $text): string
    {
        return $this->emphasis($text);
    }

    /**
     * @param string $text
     * @return string
     */
    private function emphasisWithColor(string $text): string
    {
        return $this->emphasis($text, ['style' => 'color: ' . self::COLOR]);
    }

    /**
     * @param string $text
     * @param array $attributes
     * @return string
     */
    private function emphasis(string $text, array $attributes = []): string
    {
        return (string) $this->getHtml()->tag('strong', ltrim($text, '!'), $attributes);
    }

    /**
     * @return HtmlBuilder
     */
    protected function getHtml(): HtmlBuilder
    {
        return app(HtmlBuilder::class);
    }
}
