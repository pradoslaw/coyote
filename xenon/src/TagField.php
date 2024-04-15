<?php
namespace Xenon;

readonly class TagField implements ViewItem
{
    public function __construct(
        public string $htmlTag,
        public string $fieldName,
    )
    {
    }
}
