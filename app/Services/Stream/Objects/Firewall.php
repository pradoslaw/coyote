<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Firewall as Model;

class Firewall extends ObjectAbstract
{
    /**
     * Additional data
     *
     * @var array
     */
    public $model;
    /**
     * @param Model $firewall
     * @return $this
     */
    public function map(Model $firewall)
    {
        $firewall->load(['user:id,name']);

        $this->id = $firewall->id;
        $this->url = route('adm.firewall.save', [$firewall->id], false);
        $this->displayName = str_limit($firewall->reason);
        $this->model = array_only($firewall->toArray(), ['email', 'ip', 'user']);

        return $this;
    }
}
