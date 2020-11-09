<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Microblog as Model;
use Coyote\Services\UrlBuilder;

class Microblog extends ObjectAbstract
{
    /**
     * @param Model $microblog
     * @return $this
     */
    public function map(Model $microblog)
    {
        $this->id = $microblog->id;
        $this->url = UrlBuilder::microblog($microblog);
        $this->displayName = excerpt($microblog->html);

        return $this;
    }
}
