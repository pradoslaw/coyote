<?php
namespace Coyote\Services\Parser\Parsers;

use HTMLPurifier;
use HTMLPurifier_Config;

class Purifier implements Parser
{
    private HTMLPurifier_Config $config;

    public function __construct(array $overrideAllowedHtml = null)
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->autoFinalize = false;
        $config->loadArray(config('purifier'));

        $this->config = HTMLPurifier_Config::inherit($config);
        $this->config->autoFinalize = false;
        if ($overrideAllowedHtml !== null) {
            $this->config->set('HTML.Allowed', implode(',', $overrideAllowedHtml));
        }
    }

    public function parse(string $text): string
    {
        $def = $this->config->getHTMLDefinition(true);
        $def->addAttribute('a', 'data-user-id', 'Number');
        $def->addAttribute('iframe', 'allowfullscreen', 'Bool');

        $mark = $def->addElement('mark', 'Inline', 'Inline', 'Common');
        $mark->excludes = ['mark' => true];

        return (new HTMLPurifier)->purify($text, $this->config);
    }
}
