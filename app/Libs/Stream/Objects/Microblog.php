<?php

namespace Coyote\Stream\Objects;

use Coyote\Microblog as Model;

class Microblog extends Object
{
    public $objectType = 'microblog';

    public function map(Model $microblog)
    {
        $this->id = $microblog->id;
        $this->url = route('microblog.view', [$microblog->id], false);
        $this->displayName = excerpt($microblog->text);

        return $this;
    }
}
