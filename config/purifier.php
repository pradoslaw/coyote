<?php

return [
    'Core.Encoding'        => 'UTF-8',
    'Cache.SerializerPath' => storage_path('app/purifier'),
    'HTML.Allowed' => 'b,strong,i,em,u,a[href|title|data-user-id|class],p,br,ul,ol,li,span,' .
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
];
