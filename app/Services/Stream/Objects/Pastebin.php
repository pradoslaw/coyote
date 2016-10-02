<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Pastebin as Model;

class Pastebin extends ObjectAbstract
{
    /**
     * @param Model $pastebin
     * @return $this
     */
    public function map(Model $pastebin)
    {
        $this->id = $pastebin->id;
        $this->url = route('pastebin.show', [$pastebin->id], false);
        $this->displayName = excerpt($pastebin->text);

        return $this;
    }
}
