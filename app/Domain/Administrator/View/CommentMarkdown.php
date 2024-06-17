<?php
namespace Coyote\Domain\Administrator\View;

use Coyote\Domain\Html;
use Coyote\Services\Parser\Factories\CommentFactory;

class CommentMarkdown extends Html
{
    public function __construct(
        private string $markdown,
        private int    $authorId,
    )
    {
    }

    protected function toHtml(): string
    {
        $factory = new CommentFactory(app(), $this->authorId);
        return $factory->parse($this->markdown);
    }
}
