<?php
namespace Coyote\Services\TwigBridge\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Vue extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('vueBoolean', $this->vueBoolean(...)),
        ];
    }

    private function vueBoolean(bool $value): string
    {
        return $value ? 'true' : 'false';
    }
}
