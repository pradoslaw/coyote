<?php

namespace Coyote\Services\Stream\Objects;

class Person extends Object
{
    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (!$data) {
            $data = auth()->user()->toArray();
        }

        $this->id = $data['id'];
        $this->displayName = $data['name'];
        $this->url = route('profile', [$this->id], false);

        if (!empty($data['photo'])) {
            $this->image = $data['photo'];
        }
        parent::__construct($data);
    }
}
