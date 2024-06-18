<?php
namespace Coyote\Domain\Administrator\View\Html;

use Coyote\Domain\Html;

class PostMarkdown extends Html
{
    public function __construct(private string $markdown)
    {
    }

    protected function toHtml(): string
    {
        return app('parser.post')->parse($this->markdown);
    }
}
