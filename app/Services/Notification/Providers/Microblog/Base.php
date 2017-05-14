<?php

namespace Coyote\Services\Notification\Providers\Microblog;

use Coyote\Services\Notification\Providers\Provider;
use Coyote\Services\Notification\Providers\ProviderInterface;

abstract class Base extends Provider implements ProviderInterface
{
    /**
     * @var int
     */
    protected $microblogId;

    /**
     * @param int $microblogId
     * @return $this
     */
    public function setMicroblogId($microblogId)
    {
        $this->microblogId = $microblogId;
        return $this;
    }

    /**
     * @return int
     */
    public function getMicroblogId()
    {
        return $this->microblogId;
    }

    /**
     * Generowanie unikalnego ciagu znakow dla wpisu na mikro
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5($this->typeId . $this->subject . $this->microblogId), 16);
    }
}
