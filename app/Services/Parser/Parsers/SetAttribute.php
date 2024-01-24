<?php
namespace Coyote\Services\Parser\Parsers;

class SetAttribute extends \HTMLPurifier_AttrTransform
{
    public function __construct(private string $attribute, private string $value)
    {
    }

    public function transform($attr, $config, $context): array
    {
        $attr[$this->attribute] = $this->value;
        return $attr;
    }
}
