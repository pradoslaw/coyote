<?php

namespace Coyote\Stream\Objects;

class Person extends Object
{
    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $user = auth()->user();

        $this->id = $user->id;
        $this->displayName = $user->name;
        $this->url = route('profile', [$this->id], false);

        if ($user->photo) {
            $this->image = $user->photo;
        }
        parent::__construct($data);
    }
}
