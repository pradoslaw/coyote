<?php

namespace Coyote\Services;

use Coyote\Services\Forum\UserDefined;
use Coyote\User;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

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
        $signer = new Sha256();
        $allowed = array_pluck($this->userDefined->getAllowedForums($user), 'id');

        $token = (new \Lcobucci\JWT\Builder())
            ->issuedAt(now()->timestamp)
            ->expiresAt(now()->addDays(7)->timestamp)
            ->issuedBy($user->id)
            ->withClaim('channel', "user:$user->id")
            ->withClaim('allowed', $allowed)
            ->getToken($signer, new Key(config('app.key')));

        return (string) $token;
    }
}
