<?php

namespace Coyote\Services\Parser\Parsers;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Class Purifier
 */
class Purifier implements ParserInterface
{
    /**
     * @var HTMLPurifier_Config
     */
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

    /**
     * @param $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->config->set($key, $value);
        return $this;
    }

    /**
     * @param string $text
     * @return string
     */
    public function parse(string $text): string
    {
        $def = $this->config->getHTMLDefinition(true);
        $def->addAttribute('a', 'data-user-id', 'Number');

        return (new HTMLPurifier())->purify($text, $this->config);
    }
}
