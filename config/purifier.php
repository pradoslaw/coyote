<?php
/**
 * Ok, glad you are here
 * first we get a config instance, and set the settings
 * $config = HTMLPurifier_Config::createDefault();
 * $config->set('Core.Encoding', $this->config->get('purifier.encoding'));
 * $config->set('Cache.SerializerPath', $this->config->get('purifier.cachePath'));
 * if ( ! $this->config->get('purifier.finalize')) {
 *     $config->autoFinalize = false;
 * }
 * $config->loadArray($this->getConfig());
 *
 * You must NOT delete the default settings
 * anything in settings should be compacted with params that needed to instance HTMLPurifier_Config.
 *
 * @link http://htmlpurifier.org/live/configdoc/plain.html
 */

return [
    'Core.Encoding'            => 'UTF-8',
    'Cache.SerializerPath'     => storage_path('app/purifier'),
    // zakomentowalem te linie poniewaz przez nia usuwana byla zawartosc znacznika <blockquote>
//    'HTML.Doctype'             => 'XHTML 1.0 Strict',
    'HTML.Allowed'             => 'b,strong,i,em,u,a[href|title|data-user-id|class],p,br,ul,ol[start],li,span[style|title],img[width|height|alt|src|title],sub,sup,pre,code[class],div[class],kbd,h1,h2,h3,h4,h5,h6,blockquote,del,table[summary|class],thead,tbody,tr,th[abbr],td[abbr],hr,dfn,var,samp,iframe[src|class|allowfullscreen]',
    'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,color,background-color,background-image,text-align',
    'AutoFormat.AutoParagraph' => false,
    // nie usuwaj pustych atrybutow typu <a></a>
    'AutoFormat.RemoveEmpty'   => false,
    'HTML.TidyLevel'           => 'none',
    'Core.ConvertDocumentToFragment' => false,
    // nie usuwamy niepoprawnych znacznikow. jedynie zastepujemy znaki < oraz >
    'Core.EscapeInvalidTags'    => true,
    'Core.HiddenElements'       => [],
    'Output.CommentScriptContents' => false,
    'Output.FixInnerHTML'       => false,
    // dzieki temu ustawieniu znacznik <test> nie zostanie przeksztalcony do <text />
    // trzeba monitorowac to ustawienie, poniewaz moze psuc parsowanie atrybutow
    'Core.LexerImpl'            => 'DirectLex',
    'Core.AggressivelyFixLt'    => false,
    'Output.Newline'            => "\n",

    'HTML.SafeIframe'           => true,
    'URI.SafeIframeRegexp'      => '%^(https?:)?//(youtube(?:-nocookie)?\.com/embed/)%'
];
