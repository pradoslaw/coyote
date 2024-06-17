<?php
namespace Coyote\Domain\Administrator\View;

use Coyote\Domain\Html;
use Coyote\Domain\StringHtml;

class PostMarkdown
{
    public function __construct(private string $markdown)
    {
    }

    public function contentHtml(): Html
    {
        return new StringHtml($this->postHtmlString());
    }

    public function previewHtml(): Html
    {
        return new SubstringHtml($this->contentHtml(), 100);
    }

    private function postHtmlString(): string
    {
        return app('parser.post')->parse($this->markdown);
    }
}
