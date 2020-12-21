<?php

namespace Coyote\Services;

use Coyote\Services\Forum\UserDefined;
use Coyote\User;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Configuration;

class JwtToken
{
    /**
     * @var UserDefined
     */
    private $userDefined;

    /**
     * JwtToken constructor.
     * @param UserDefined $userDefined
     */
    public function __construct(UserDefined $userDefined)
    {
        $this->userDefined = $userDefined;
    }

    /**
     * @param User $user
     * @return string
     */
    public function token(User $user): string
    {
        $allowed = array_pluck($this->userDefined->allowedForums($user), 'id');

        $key = InMemory::plainText(config('app.key'));

        $configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            $key
        );

        $now   = new \DateTimeImmutable('@' . time());

        $token = $configuration
            ->builder()
            ->expiresAt($now->modify('+30 days'))
            ->issuedBy($user->id)
            ->withClaim('allowed', $allowed)
            ->getToken($configuration->signer(), $configuration->signingKey());

        return (string) $token->toString();
    }
}
