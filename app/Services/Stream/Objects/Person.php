<?php

namespace Coyote\Services\Stream\Objects;

class Person extends ObjectAbstract
{
    /**
     * @var string
     */
    public $image;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (empty($data)) {
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
