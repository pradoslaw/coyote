<?php

namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Collective\Html\HtmlBuilder;

class Emphasis extends Parser implements ParserInterface
{
    const COLOR = '#B60016';

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $ability;

    /**
     * @var UserRepository
     */
    protected $user;

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
    public function setAbility($ability)
    {
        $this->ability = $ability;

        return $this;
    }

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        if (substr($text, 0, 1) !== '!') {
            return $text;
        }

        $user = $this->user->find($this->userId, ['id']);

        if (!$user || !$user->can($this->ability)) {
            return $text;
        }

        if ('!!' === substr($text, 0, 2)) {
            return $this->emphasisWithColor($text);
        } else {
            return $this->emphasisWithBold($text);
        }
    }

    /**
     * @param string $text
     * @return string
     */
    private function emphasisWithBold($text)
    {
        return $this->emphasis($text);
    }

    /**
     * @param string $text
     * @return string
     */
    private function emphasisWithColor($text)
    {
        return $this->emphasis($text, ['style' => 'color: ' . self::COLOR]);
    }

    /**
     * @param string $text
     * @param array $attributes
     * @return string
     */
    private function emphasis($text, array $attributes = [])
    {
        return (string) $this->getHtml()->tag('strong', ltrim($text, '!'), $attributes);
    }

    /**
     * @return HtmlBuilder
     */
    protected function getHtml()
    {
        return app(HtmlBuilder::class);
    }
}
