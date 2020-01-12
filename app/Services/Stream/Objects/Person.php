<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\User;

class Person extends ObjectAbstract
{
    /**
     * @var string
     */
    public $image;

    /**
     * Person constructor.
     * @param User|null $user
     */
    public function __construct(User $user = null)
    {
        if (empty($user)) {
            $user = auth()->user();
        }

        $this->id = $user->id;
        $this->displayName = $user->name;
        $this->url = route('profile', [$this->id], false);

        if (!empty($user->photo)) {
            $this->image = $user->photo ? (string) $user->photo : null;
        }

        parent::__construct($user->only(['ip', 'browser']));
    }
}
