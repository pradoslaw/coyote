<?php

namespace Coyote\Services\Parser;

use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Extension\Mention\Generator\MentionGeneratorInterface;
use League\CommonMark\Extension\Mention\Mention;
use League\CommonMark\Node\Inline\AbstractInline;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use League\CommonMark\Node\Inline\Text;

class MentionGenerator implements MentionGeneratorInterface
{
    public function __construct(private UserRepository $user)
    {
    }

    public function generateMention(Mention $mention): ?AbstractInline
    {
        $identifier = $this->stripIdentifier($mention->getIdentifier());
        $user = $this->user->findByName($identifier);

        if ($user) {
            $mention->setUrl(route('profile', [$user->id]));
            $mention->setLabel('@' . $user->name);
            $mention->data->set('attributes', ['class' => 'mention', 'data-user-id' => (string) $user->id]);

            return $mention;
        }

        return new Text('@' . $identifier);
    }

    private function stripIdentifier(string $identifier): string
    {
        return trim($identifier, '{}');
    }
}
