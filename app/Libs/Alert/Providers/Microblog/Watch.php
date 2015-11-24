<?php

namespace Coyote\Alert\Providers\Microblog;

use Coyote\Alert;
use Coyote\Alert\Providers\Provider;

/**
 * Class Watch
 * @package Coyote\Alert\Providers\Microblog
 */
class Watch extends Provider implements Alert\Providers\ProviderInterface
{
    const ID = Alert::MICROBLOG_WATCH;
//    const EMAIL = 'emails.alerts.microblog.vote';

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
