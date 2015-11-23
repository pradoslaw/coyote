<?php

namespace Coyote\Alert;

use Coyote\Alert\Providers\ProviderInterface;

/**
 * Class Alert
 * @package Coyote\Alert
 */
class Alert
{
    /**
     * @var ProviderInterface
     */
    protected $alerts = [];

    /**
     * @param ProviderInterface|null $alert
     */
    public function __construct(ProviderInterface $alert = null)
    {
        if ($alert) {
            $this->alerts[] = $alert;
        }
    }

    /**
     * @param ProviderInterface $alert
     * @return $this
     */
    public function attach(ProviderInterface $alert)
    {
        $this->alerts[] = $alert;
        return $this;
    }

    /**
     * Generuje i wysyla powiadomienia
     */
    public function notify()
    {
        foreach ($this->alerts as $alert) {
            $alert->notify();
        }
    }
}
