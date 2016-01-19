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
    'Cache.SerializerPath'     => getenv('APP_ENV') === 'local' ? null : storage_path('app/purifier'),
    'HTML.Doctype'             => 'XHTML 1.0 Strict',
    'HTML.Allowed'             => 'b,strong,i,em,a[href|title|data-user-id|class],p,br,ul,ol,li,span[style],img[width|height|alt|src|title],sub,sup,pre,code[class],kbd,h2,h3,h4,h5,h6,blockquote',
    'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,color,background-color,text-align',
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
//    'Core.LexerImpl'            => 'DirectLex', // <-- nie wlaczac. psuje parsowanie atrybutow
    'Core.AggressivelyFixLt'    => false,
    'Output.Newline'            => "\n"
];
