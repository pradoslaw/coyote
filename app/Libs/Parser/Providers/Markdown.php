<?php

namespace Coyote\Parser\Providers;

use Coyote\Repositories\Contracts\UserRepositoryInterface as User;

/**
 * Class Markdown
 * @package Coyote\Parser\Providers
 */
class Markdown extends \ParsedownExtra implements ProviderInterface
{
    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     * @throws \Exception
     */
    public function __construct(User $user)
    {
        parent::__construct();

        $this->InlineTypes['@'][] = 'UserTag';
        $this->inlineMarkerList .= '@';

        $this->user = $user;
    }

    protected function inlineUserTag($excerpt)
    {
        $text = &$excerpt['text'];
        $start = strpos($text, '@');

        if (isset($text[$start + 1])) {
            $exitChar = $text[$start + 1] === '{' ? '}' : ' ';
            $end = strpos($text, $exitChar, $start);

            if ($end === false) {
                $end = mb_strlen($text) - $start;
            }

            $length = $end - $start;
            $start += 1;
            $end -= 1;

            if ($exitChar == '}') {
                $start += 1;
                $end -= 1;

                $length += 1;
            }

            $name = substr($text, $start, $end);
            $user = $this->user->findByName($name);

            if ($user) {
                return [
                    'extent' => $length,
                    'element' => [
                        'name' => 'a',
                        'text' => '@' . $name,
                        'attributes' => [
                            'href' => route('profile', [$user->id]),
                            'data-user-id' => $user->id
                        ]
                    ]
                ];
            }
        }
    }

    public function parse($text)
    {
        return $this->setBreaksEnabled(true)->text($text);
    }
}
