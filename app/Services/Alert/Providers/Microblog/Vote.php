<?php

namespace Coyote\Services\Alert\Providers\Microblog;

use Coyote\Alert;
use Coyote\Services\Alert\Providers\Provider;
use Coyote\Services\Alert\Providers\ProviderInterface;

/**
 * Class Vote
 */
class Vote extends Provider implements ProviderInterface
{
    const ID = Alert::MICROBLOG_VOTE;
    const EMAIL = 'emails.alerts.microblog.vote';

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
