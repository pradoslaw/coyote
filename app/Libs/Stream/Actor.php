<?php

namespace Coyote\Stream;

use Coyote\Stream\Objects\Object;
use Coyote\User;

class Actor extends Object
{
    /**
     * @param User $user
     * @param array $data
     */
    public function __construct($user, array $data = [])
    {
        if ($user && $user instanceof User) {
            $data = array_merge([
                'displayName'   => $user->name,
                'id'            => $user->id,
                'url'           => route('profile', [$user->id], false),
                'image'         => $user->photo
            ], $data);
        }

        parent::__construct($data);
    }
}
