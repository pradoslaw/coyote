<?php
namespace Coyote\Services\Parser\Extensions;

use Coyote\Repositories\Eloquent\PageRepository;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;

class InternalLinkExtension implements ExtensionInterface
{
    public function __construct(private PageRepository $page, private string $host)
    {
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(DocumentParsedEvent::class,
            new InternalLinkProcessor($this->page, $this->host),
            -50);
    }
}
