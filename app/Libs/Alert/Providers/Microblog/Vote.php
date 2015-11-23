<?php

namespace Coyote\Alert\Providers\Microblog;

use Coyote\Alert;
use Coyote\Alert\Providers\Provider;

/**
 * Class Vote
 * @package Coyote\Alert\Providers\Microblog
 */
class Vote extends Provider implements Alert\Providers\ProviderInterface
{
    const ID = Alert::MICROBLOG_VOTE;

    /**
     * @var int
     */
    protected $microblogId;

    public function setMicroblogId($microblogId)
    {
        $this->microblogId = $microblogId;
    }

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
