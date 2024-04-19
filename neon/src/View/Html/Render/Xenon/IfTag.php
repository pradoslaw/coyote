<?php
namespace Neon\View\Html\Render\Xenon;

use Neon\View\Html\Tag;

readonly class IfTag extends \Xenon\If_ implements Tag
{
    public function __construct(string $conditionField, array $body, array $else)
    {
        parent::__construct($conditionField, $body, $else);
    }

    public function parentClass(): ?string
    {
        return null;
    }
}
