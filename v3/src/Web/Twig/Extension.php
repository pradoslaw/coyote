<?php
namespace V3\Web\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Extension extends AbstractExtension
{
    private static array $functions = [];

    public static function addTwigFunction(string $name, callable $function): void
    {
        self::$functions[] = new TwigFunction($name, $function);
    }

    public function getFunctions(): array
    {
        return self::$functions;
    }
}
