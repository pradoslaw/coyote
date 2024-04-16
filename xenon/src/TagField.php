<?php
namespace Xenon;

readonly class TagField extends Tag
{
    public function __construct(string $htmlTag, string $fieldName)
    {
        parent::__construct($htmlTag, [], [new Field($fieldName)]);
    }
}
