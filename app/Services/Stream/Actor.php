<?php

namespace Coyote\Services\Stream;

use Coyote\Services\Stream\Objects\ObjectAbstract;
use Coyote\User;

class Actor extends ObjectAbstract
{
    /**
     * @param User|null $user
     * @param array $data
     */
    public function __construct($user = null, array $data = [])
    {
        if ($user && $user instanceof User) {
            $data = array_merge([
                'displayName'   => $user->name,
                'id'            => $user->id,
                'url'           => route('profile', [$user->id], false),
                'image'         => $user->photo->getFilename()
            ], $data);
        }

        parent::__construct($data);
    }
}
