<?php
namespace Coyote\Services\Parser\Extensions;

use Coyote\Repositories\Eloquent\PageRepository;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

class InternalLinkExtension implements ConfigurableExtensionInterface
{
    public function __construct(private PageRepository $page)
    {
    }

    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->addSchema('internal_link', Expect::structure([
            'internal_hosts' => Expect::type('string|string[]'),
        ]));
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(DocumentParsedEvent::class,
            new InternalLinkProcessor($this->page, $environment->getConfiguration()),
            -50);
    }
}
