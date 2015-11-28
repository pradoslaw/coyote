<?php

namespace Coyote\Parser\Providers;

/**
 * Class Purifier
 * @package Coyote\Parser\Providers
 */
class Purifier implements ProviderInterface
{
    public function parse($text)
    {
        $config = \HTMLPurifier_Config::createDefault();
        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'data-user-id', 'Number');

        return \Purifier::clean($text, $config);
    }
}
