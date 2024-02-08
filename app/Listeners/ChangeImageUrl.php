<?php
namespace Coyote\Listeners;

use Illuminate\Mail\Events\MessageSending;

class ChangeImageUrl
{
    public function handle(MessageSending $event): void
    {
        $event->message->html($this->appendHttps($event->message->getHtmlBody()));
    }

    private function appendHttps(string $html): string
    {
        $html = \mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        \libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html, \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        foreach ($dom->getElementsByTagName('img') as $image) {
            $url = $image->getAttribute('src');
            if (!\preg_match('#^\w+?://.*?#i', $url)) {
                $image->setAttribute('src', "https:$url");
            }
        }
        return \trim($dom->saveHTML());
    }
}
