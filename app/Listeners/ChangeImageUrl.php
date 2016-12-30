<?php

namespace Coyote\Listeners;

use Illuminate\Mail\Events\MessageSending;

class ChangeImageUrl
{
    /**
     * Handle the event.
     *
     * @param  MessageSending  $event
     * @return void
     */
    public function handle(MessageSending $event)
    {
        $html = $event->message->getBody();
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");

        // ignore html errors
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument;
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $images = $dom->getElementsByTagName('img');

        foreach ($images as $image) {
            $url = $image->getAttribute('src');

            if (!preg_match('#^[\w]+?://.*?#i', $url)) {
                $url = 'https:' . $url;
                $image->setAttribute('src', $url);
            }
        }

        $event->message->setBody(trim($dom->saveHTML()));
    }
}
