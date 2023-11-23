<?php

namespace Coyote\Services\Parser\Extensions;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Renderer\HtmlDecorator;

class YoutubeLinkExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(DocumentParsedEvent::class, new YoutubeLinkProcessor(), -51);
        $environment->addRenderer(Iframe::class, new HtmlDecorator(new YoutubeLinkRenderer(), 'span', ['class' => 'd-block embed-responsive embed-responsive-16by9']));
    }
}
