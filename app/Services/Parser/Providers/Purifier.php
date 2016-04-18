<?php

namespace Coyote\Services\Parser\Providers;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Class Purifier
 */
class Purifier implements ProviderInterface
{
    private $config;

    public function __construct()
    {
        // Create a new configuration object
        $config = HTMLPurifier_Config::createDefault();
        $config->autoFinalize = false;

        $config->loadArray(config('purifier'));

        $this->config = HTMLPurifier_Config::inherit($config);
        $this->config->autoFinalize = false;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function set($key, $value)
    {
        $this->config->set($key, $value);
        return $this;
    }

    public function parse($text)
    {
        $def = $this->config->getHTMLDefinition(true);
        $def->addAttribute('a', 'data-user-id', 'Number');

        return (new HTMLPurifier())->purify($text, $this->config);
    }
}
