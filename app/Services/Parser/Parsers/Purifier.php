<?php
namespace Coyote\Services\Parser\Parsers;

use HTMLPurifier;
use HTMLPurifier_Config;

class Purifier implements Parser
{
    private HTMLPurifier_Config $config;

    public function __construct(array $overrideAllowedHtml = null, private bool $canAddVideo = false)
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->autoFinalize = false;
        $config->loadArray(config('purifier'));

        $this->config = HTMLPurifier_Config::inherit($config);
        $this->config->autoFinalize = false;
        if ($overrideAllowedHtml !== null) {
            $this->config->set('HTML.Allowed', implode(',', $overrideAllowedHtml));
        }
        if ($this->canAddVideo) {
            $this->config->set('HTML.Allowed', $this->config->get('HTML.Allowed') . ',video[src]');
        }
    }

    public function parse(string $text): string
    {
        $def = $this->config->getHTMLDefinition(true);

        $anchor = $def->addBlankElement('a');
        $anchor->attr_transform_post[] = new SetAttribute('rel', 'nofollow');

        $def->addAttribute('a', 'data-user-id', 'Number');
        $def->addAttribute('iframe', 'allowfullscreen', 'Bool');

        $mark = $def->addElement('mark', 'Inline', 'Inline', 'Common');
        $mark->excludes = ['mark' => true];

        if ($this->canAddVideo) {
            $video = $def->addElement('video', 'Inline', 'Inline', 'Common', [
                'src' => 'URI',
            ]);
            $video->excludes = ['video' => true];
            $video->attr_transform_post[] = new SetAttribute('controls', 'controls');
        }
        return (new HTMLPurifier)->purify($text, $this->config);
    }
}
