<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Firewall as Model;

class Firewall extends Object
{
    /**
     * @param Model $firewall
     * @return $this
     */
    public function map(Model $firewall)
    {
        $this->id = $firewall->id;
        $this->url = route('adm.firewall.save', [$firewall->id], false);
        $this->displayName = excerpt($firewall->reason);

        return $this;
    }
}
