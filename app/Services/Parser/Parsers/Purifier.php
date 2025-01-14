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
        $config->loadArray([
            'Core.Encoding'                  => 'UTF-8',
            'Cache.SerializerPath'           => storage_path('app/purifier'),
            'HTML.Allowed'                   => 'b,strong,i,em,u,a[href|title|data-user-id|class],p,br,ul,ol,li,span,' .
                'img[width|height|alt|src|title|class],sub,sup,pre,code[class],div,kbd,mark,h1,h2,h3,h4,h5,h6,blockquote,del,' .
                'table,thead,tbody,tr,th[abbr],td[abbr],hr,dfn,var,samp,iframe[src|class|allowfullscreen],div[class]',
            'Attr.AllowedClasses'            => ['markdown-code', 'img-smile', 'youtube-player', 'mention', 'copy-button'],
            'CSS.AllowedProperties'          => 'font,font-size,font-weight,font-style,font-family,text-decoration,color,background-color,background-image,text-align',
            'AutoFormat.AutoParagraph'       => false,
            'AutoFormat.RemoveEmpty'         => false, // nie usuwaj pustych atrybutow typu <a></a>
            'HTML.TidyLevel'                 => 'none',
            'Core.ConvertDocumentToFragment' => false,
            'Core.EscapeInvalidTags'         => true, // nie usuwamy niepoprawnych znacznikow. jedynie zastepujemy znaki < oraz >
            'Core.HiddenElements'            => [],
            'Output.CommentScriptContents'   => false,
            'Output.FixInnerHTML'            => false,
            // dzieki temu ustawieniu znacznik <test> nie zostanie przeksztalcony do <text />
            // trzeba monitorowac to ustawienie, poniewaz moze psuc parsowanie atrybutow
            'Core.LexerImpl'                 => 'DirectLex',
            'Core.AggressivelyFixLt'         => false,
            'Output.Newline'                 => "\n",
            'HTML.SafeIframe'                => true,
            'URI.SafeIframeRegexp'           => '%^(https?:)?//(youtube(?:-nocookie)?\.com/embed/)%',
        ]);

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
